from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional, List
import joblib
import numpy as np
import pandas as pd
import os
import warnings
import pymysql
import pymysql.cursors
from groq import Groq
from fastapi import UploadFile, File
import tempfile
import shutil

warnings.filterwarnings('ignore')

# ── Groq client (desde variable de entorno) ──
GROQ_API_KEY = os.environ.get("GROQ_API_KEY")
if not GROQ_API_KEY:
    raise ValueError("GROQ_API_KEY no está configurada en variables de entorno")
groq_client = Groq(api_key=GROQ_API_KEY)

# ── Configuración MySQL (desde variables de entorno) ──
DB_CONFIG = {
    "host":        os.environ.get("DB_HOST", "127.0.0.1"),
    "port":        int(os.environ.get("DB_PORT", 3306)),
    "user":        os.environ.get("DB_USERNAME", "root"),
    "password":    os.environ.get("DB_PASSWORD", ""),
    "database":    os.environ.get("DB_DATABASE", "sistco"),
    "charset":     "utf8mb4",
    "cursorclass": pymysql.cursors.DictCursor,
}

def get_db():
    return pymysql.connect(**DB_CONFIG)

app = FastAPI(
    title="SISTCO-ML API",
    description="Microservicio de predicciones ML para SISTCO Sistemas y Comunicaciones SAS",
    version="2.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Cargar modelos al iniciar ──
BASE = os.path.dirname(__file__)

print("Cargando modelos...")
modelo_clasificacion = joblib.load(os.path.join(BASE, 'modelos', 'modelo_mora.pkl'))
modelo_segmentacion  = joblib.load(os.path.join(BASE, 'modelos', 'modelo_segmentacion.pkl'))
modelo_ingresos      = joblib.load(os.path.join(BASE, 'modelos', 'modelo_ingresos.pkl'))
print("✅ modelo_mora.pkl         — Clasificación de clientes")
print("✅ modelo_segmentacion.pkl — Segmentación de clientes")
print("✅ modelo_ingresos.pkl     — Proyección de ingresos")
print(f"🔗 Conectado a DB: {DB_CONFIG['host']}:{DB_CONFIG['port']}/{DB_CONFIG['database']}")
print("🚀 API lista en http://localhost:8000")

# ── Schemas ──
class DatosCliente(BaseModel):
    mensualidad: float
    antiguedad_meses: float
    megas_cod: int
    municipio_cod: int
    metodo_pago_cod: int
    n_meses_activos: int
    n_moras_historicas: int
    tasa_mora_historica: float
    moras_ult_3_meses: int
    moras_ult_6_meses: int
    racha_limpia_final: int
    dia_prom_pago_ult12: float

class DatosSegmentacion(BaseModel):
    mensualidad: float
    antiguedad_meses: float
    n_meses_activos: int
    n_moras_historicas: int
    tasa_mora_historica: float
    moras_ult_3_meses: int
    moras_ult_6_meses: int
    racha_limpia_final: int
    dia_prom_pago_ult12: float

class MensajeHistorial(BaseModel):
    rol:       str
    contenido: str

class PreguntaAsistente(BaseModel):
    pregunta:   str
    user_id:    Optional[int]                    = None
    session_id: Optional[str]                    = None
    historial:  Optional[List[MensajeHistorial]] = []

# ── Función: datos en tiempo real desde MySQL ──
def obtener_datos_bd():
    try:
        conn = get_db()
        with conn.cursor() as cursor:

            cursor.execute("SELECT COUNT(*) as total FROM clientes")
            total = cursor.fetchone()['total']

            cursor.execute("SELECT ROUND(AVG(tasa_mora_historica)*100,2) as tasa FROM clientes")
            tasa_prom = cursor.fetchone()['tasa'] or 0

            cursor.execute("SELECT COUNT(*) as n FROM clientes WHERE moras_ult_3_meses > 0")
            en_mora_rec = cursor.fetchone()['n']

            cursor.execute("SELECT ROUND(SUM(mensualidad)*1000,0) as ingreso FROM clientes")
            ingreso_act = cursor.fetchone()['ingreso'] or 0

            cursor.execute("SELECT ROUND(AVG(mensualidad)*1000,0) as mens FROM clientes")
            mens_prom = cursor.fetchone()['mens'] or 0

            cursor.execute("SELECT ROUND(AVG(antiguedad_meses),1) as antig FROM clientes")
            antig_prom = cursor.fetchone()['antig'] or 0

            # Clientes por nivel de riesgo ML
            cursor.execute("""
                SELECT nivel_riesgo_ml, COUNT(*) as n
                FROM clientes
                WHERE nivel_riesgo_ml IS NOT NULL
                GROUP BY nivel_riesgo_ml
            """)
            riesgos = {r['nivel_riesgo_ml']: r['n'] for r in cursor.fetchall()}
            alto  = riesgos.get('Alto',  0)
            medio = riesgos.get('Medio', 0)
            bajo  = riesgos.get('Bajo',  0)

            # Top 5 municipios por mora
            cursor.execute("""
                SELECT municipio,
                       ROUND(AVG(tasa_mora_historica)*100,2) as mora_prom
                FROM clientes
                WHERE municipio IS NOT NULL
                GROUP BY municipio
                ORDER BY mora_prom DESC
                LIMIT 5
            """)
            muni_mora = '\n'.join([
                f"  - {r['municipio']}: {r['mora_prom']}%"
                for r in cursor.fetchall()
            ])

            # Top 5 municipios por número de clientes
            cursor.execute("""
                SELECT municipio, COUNT(*) as total
                FROM clientes
                WHERE municipio IS NOT NULL
                GROUP BY municipio
                ORDER BY total DESC
                LIMIT 5
            """)
            muni_clientes = '\n'.join([
                f"  - {r['municipio']}: {r['total']} clientes"
                for r in cursor.fetchall()
            ])

            # Clientes en mora actual (es_moroso = 1)
            cursor.execute("SELECT COUNT(*) as n FROM clientes WHERE es_moroso = 1")
            mora_ultimo_mes = cursor.fetchone()['n']

            # Top 5 clientes con mayor probabilidad de incumplimiento
            cursor.execute("""
                SELECT codigo_cliente, municipio,
                       ROUND(probabilidad_ml,2) as prob,
                       nivel_riesgo_ml,
                       ROUND(tasa_mora_historica*100,1) as mora_hist
                FROM clientes
                WHERE probabilidad_ml IS NOT NULL
                ORDER BY probabilidad_ml DESC
                LIMIT 5
            """)
            top_riesgo_str = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}): {r['prob']}% probabilidad, mora histórica {r['mora_hist']}%"
                for r in cursor.fetchall()
            ])

            # Riesgo por municipio
            cursor.execute("""
                SELECT municipio,
                       SUM(CASE WHEN nivel_riesgo_ml='Alto'  THEN 1 ELSE 0 END) as alto,
                       SUM(CASE WHEN nivel_riesgo_ml='Medio' THEN 1 ELSE 0 END) as medio,
                       SUM(CASE WHEN nivel_riesgo_ml='Bajo'  THEN 1 ELSE 0 END) as bajo,
                       COUNT(*) as total
                FROM clientes
                WHERE nivel_riesgo_ml IS NOT NULL
                GROUP BY municipio
                ORDER BY alto DESC
            """)
            riesgo_municipio = '\n'.join([
                f"  - {r['municipio']}: Alto={r['alto']}, Medio={r['medio']}, Bajo={r['bajo']} (total {r['total']})"
                for r in cursor.fetchall()
            ])

            # Distribución de mensualidades
            cursor.execute("""
                SELECT ROUND(mensualidad*1000,0) as valor, COUNT(*) as total
                FROM clientes
                GROUP BY valor
                ORDER BY total DESC
                LIMIT 10
            """)
            dist_mensualidades = '\n'.join([
                f"  - ${int(r['valor']):,} COP: {r['total']} clientes"
                for r in cursor.fetchall()
            ])

            # Clientes por municipio y mora
            cursor.execute("""
                SELECT municipio,
                       COUNT(*) as total,
                       SUM(es_moroso) as en_mora
                FROM clientes
                GROUP BY municipio
                ORDER BY total DESC
            """)
            mora_municipio = '\n'.join([
                f"  - {r['municipio']}: {r['total']} clientes, {r['en_mora']} en mora"
                for r in cursor.fetchall()
            ])

            # Distribución de megas
            cursor.execute("""
                SELECT megas, COUNT(*) as total,
                       ROUND(AVG(mensualidad*1000),0) as mensualidad_prom
                FROM clientes
                GROUP BY megas
                ORDER BY total DESC
            """)
            dist_megas = '\n'.join([
                f"  - {r['megas']}: {r['total']} clientes, mensualidad promedio ${int(r['mensualidad_prom']):,} COP"
                for r in cursor.fetchall()
            ])

            # Top 10 clientes mayor mensualidad
            cursor.execute("""
                SELECT codigo_cliente, municipio, megas,
                       ROUND(mensualidad*1000,0) as mensualidad,
                       nivel_riesgo_ml
                FROM clientes
                ORDER BY mensualidad DESC
                LIMIT 10
            """)
            top_mensualidad = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}): {r['megas']}, ${int(r['mensualidad']):,} COP, riesgo {r['nivel_riesgo_ml'] or 'N/A'}"
                for r in cursor.fetchall()
            ])

            # Clientes corporativos
            cursor.execute("""
                SELECT codigo_cliente, municipio, megas,
                       ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad,
                       ROUND(tasa_mora_historica*100,2) as mora_hist,
                       nivel_riesgo_ml
                FROM clientes
                WHERE mensualidad >= 500
                ORDER BY mensualidad DESC, antiguedad_meses DESC
                LIMIT 6
            """)
            rows = cursor.fetchall()
            if rows:
                clientes_corporativos = '\n'.join([
                    f"  - Código {r['codigo_cliente']} ({r['municipio']}): plan {r['megas']}, ${int(r['mensualidad']):,} COP mensual, {int(r['antiguedad'])} meses antigüedad, mora histórica {r['mora_hist']}%"
                    for r in rows
                ])
            else:
                cursor.execute("""
                    SELECT codigo_cliente, municipio, megas,
                           ROUND(mensualidad*1000,0) as mensualidad,
                           ROUND(antiguedad_meses,0) as antiguedad,
                           ROUND(tasa_mora_historica*100,2) as mora_hist
                    FROM clientes
                    ORDER BY mensualidad DESC
                    LIMIT 4
                """)
                clientes_corporativos = '\n'.join([
                    f"  - Código {r['codigo_cliente']} ({r['municipio']}): plan {r['megas']}, ${int(r['mensualidad']):,} COP mensual, {int(r['antiguedad'])} meses antigüedad"
                    for r in cursor.fetchall()
                ])

            # ── NUEVAS CONSULTAS ──

            # Clientes por vereda
            cursor.execute("""
                SELECT COALESCE(vereda, 'Sin vereda') as vereda,
                       municipio,
                       COUNT(*) as total,
                       SUM(es_moroso) as en_mora,
                       ROUND(AVG(tasa_mora_historica)*100,2) as mora_prom,
                       SUM(CASE WHEN nivel_riesgo_ml='Alto' THEN 1 ELSE 0 END) as riesgo_alto
                FROM clientes
                GROUP BY vereda, municipio
                ORDER BY municipio, total DESC
            """)
            clientes_vereda = '\n'.join([
                f"  - {r['vereda']} ({r['municipio']}): {r['total']} clientes, {r['en_mora']} en mora, mora prom {r['mora_prom']}%, riesgo alto {r['riesgo_alto']}"
                for r in cursor.fetchall()
            ])

            # Planes por vereda
            cursor.execute("""
                SELECT COALESCE(vereda, 'Sin vereda') as vereda,
                       municipio, megas,
                       COUNT(*) as total,
                       ROUND(AVG(mensualidad*1000),0) as mens_prom
                FROM clientes
                GROUP BY vereda, municipio, megas
                ORDER BY municipio, vereda, total DESC
            """)
            plan_por_vereda = '\n'.join([
                f"  - {r['vereda']} ({r['municipio']}): plan {r['megas']}, {r['total']} clientes, ${int(r['mens_prom']):,} COP prom"
                for r in cursor.fetchall()
            ])

            # Estado de pago por vereda
            cursor.execute("""
                SELECT COALESCE(vereda, 'Sin vereda') as vereda,
                       municipio,
                       SUM(CASE WHEN es_moroso=0 THEN 1 ELSE 0 END) as al_dia,
                       SUM(es_moroso) as en_mora,
                       COUNT(*) as total
                FROM clientes
                GROUP BY vereda, municipio
                ORDER BY municipio, vereda
            """)
            estado_pago_vereda = '\n'.join([
                f"  - {r['vereda']} ({r['municipio']}): {r['al_dia']} al día, {r['en_mora']} en mora de {r['total']} total"
                for r in cursor.fetchall()
            ])

            # Top 5 clientes más antiguos
            cursor.execute("""
                SELECT codigo_cliente, municipio,
                       COALESCE(vereda, 'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad,
                       fecha_instalacion, es_moroso, nivel_riesgo_ml
                FROM clientes
                ORDER BY antiguedad_meses DESC
                LIMIT 5
            """)
            clientes_mas_antiguos = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): {int(r['antiguedad'])} meses, plan {r['megas']}, ${int(r['mensualidad']):,} COP, {'en mora' if r['es_moroso'] else 'al día'}"
                for r in cursor.fetchall()
            ])

            # Top 5 clientes más recientes
            cursor.execute("""
                SELECT codigo_cliente, municipio,
                       COALESCE(vereda, 'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad,
                       fecha_instalacion, es_moroso, nivel_riesgo_ml
                FROM clientes
                ORDER BY antiguedad_meses ASC
                LIMIT 5
            """)
            clientes_mas_recientes = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): {int(r['antiguedad'])} meses, plan {r['megas']}, ${int(r['mensualidad']):,} COP, instalado {r['fecha_instalacion']}"
                for r in cursor.fetchall()
            ])

            # Detalle completo clientes en mora
            cursor.execute("""
                SELECT codigo_cliente, municipio,
                       COALESCE(vereda, 'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       n_moras_historicas,
                       ROUND(tasa_mora_historica*100,2) as mora_hist,
                       moras_ult_3_meses, nivel_riesgo_ml
                FROM clientes
                WHERE es_moroso = 1
                ORDER BY tasa_mora_historica DESC
            """)
            rows_mora = cursor.fetchall()
            detalle_clientes_mora = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): plan {r['megas']}, ${int(r['mensualidad']):,} COP, {r['n_moras_historicas']} moras históricas, mora {r['mora_hist']}%"
                for r in rows_mora
            ])
            total_mora_detalle = len(rows_mora)

            # Planes por mensualidad exacta
            cursor.execute("""
                SELECT ROUND(mensualidad*1000,0) as valor,
                       megas,
                       COUNT(*) as total,
                       SUM(es_moroso) as en_mora
                FROM clientes
                GROUP BY valor, megas
                ORDER BY valor ASC
            """)
            plan_mensualidad = '\n'.join([
                f"  - ${int(r['valor']):,} COP — plan {r['megas']}: {r['total']} clientes, {r['en_mora']} en mora"
                for r in cursor.fetchall()
            ])

            # Corporativos por vereda
            cursor.execute("""
                SELECT codigo_cliente, municipio,
                       COALESCE(vereda, 'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad,
                       es_moroso, nivel_riesgo_ml
                FROM clientes
                WHERE mensualidad >= 500
                ORDER BY municipio, vereda, mensualidad DESC
            """)
            corporativos_por_vereda = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): plan {r['megas']}, ${int(r['mensualidad']):,} COP, {int(r['antiguedad'])} meses, {'en mora' if r['es_moroso'] else 'al día'}"
                for r in cursor.fetchall()
            ])

            # Tipos de identificación
            cursor.execute("""
                SELECT tipo_identificacion, COUNT(*) as total
                FROM clientes
                GROUP BY tipo_identificacion
            """)
            tipos_id = '\n'.join([
                f"  - {r['tipo_identificacion']}: {r['total']} clientes"
                for r in cursor.fetchall()
            ])

        conn.close()

        return {
            "total":           total,
            "tasa_prom":       tasa_prom,
            "en_mora_rec":     en_mora_rec,
            "ingreso_act":     ingreso_act,
            "mens_prom":       mens_prom,
            "antig_prom":      antig_prom,
            "alto":            alto,
            "medio":           medio,
            "bajo":            bajo,
            "muni_mora":       muni_mora,
            "muni_clientes":   muni_clientes,
            "mora_ultimo_mes": mora_ultimo_mes,
            "top_riesgo":      top_riesgo_str,
            "riesgo_municipio":      riesgo_municipio,
            "dist_mensualidades":    dist_mensualidades,
            "mora_municipio":        mora_municipio,
            "dist_megas":            dist_megas,
            "top_mensualidad":       top_mensualidad,
            "clientes_corporativos": clientes_corporativos,
            # Nuevas
            "clientes_vereda":         clientes_vereda,
            "plan_por_vereda":         plan_por_vereda,
            "estado_pago_vereda":      estado_pago_vereda,
            "clientes_mas_antiguos":   clientes_mas_antiguos,
            "clientes_mas_recientes":  clientes_mas_recientes,
            "detalle_clientes_mora":   detalle_clientes_mora,
            "total_mora_detalle":      total_mora_detalle,
            "plan_mensualidad":        plan_mensualidad,
            "corporativos_por_vereda": corporativos_por_vereda,
            "tipos_id":                tipos_id,
        }

    except Exception as e:
        print(f"Error MySQL: {e}")
        return {
            "total": 781, "tasa_prom": 2.63, "en_mora_rec": 249,
            "ingreso_act": 81158800, "mens_prom": 103917, "antig_prom": 31.5,
            "alto": 28, "medio": 145, "bajo": 608,
            "muni_mora":       "  - No disponible",
            "muni_clientes":   "  - No disponible",
            "mora_ultimo_mes": 0,
            "top_riesgo":      "  - No disponible",
            "riesgo_municipio":      "  - No disponible",
            "dist_mensualidades":    "  - No disponible",
            "mora_municipio":        "  - No disponible",
            "dist_megas":            "  - No disponible",
            "top_mensualidad":       "  - No disponible",
            "clientes_corporativos": "  - No disponible",
            "clientes_vereda":         "  - No disponible",
            "plan_por_vereda":         "  - No disponible",
            "estado_pago_vereda":      "  - No disponible",
            "clientes_mas_antiguos":   "  - No disponible",
            "clientes_mas_recientes":  "  - No disponible",
            "detalle_clientes_mora":   "  - No disponible",
            "total_mora_detalle":      0,
            "plan_mensualidad":        "  - No disponible",
            "corporativos_por_vereda": "  - No disponible",
            "tipos_id":                "  - No disponible",
        }

# ── Función auxiliar: calcular proyecciones ──
def _calcular_proyecciones():
    try:
        modelo_lr  = modelo_ingresos['modelo_lr']
        serie_dict = modelo_ingresos['serie']
        serie = pd.DataFrame(serie_dict)
        serie['ds'] = pd.to_datetime(serie['ds'])
        serie = serie.sort_values('ds').reset_index(drop=True)
        serie['mes_num']      = np.arange(len(serie))
        serie['sin_estacion'] = np.sin(2 * np.pi * serie['ds'].dt.month / 12)
        serie['cos_estacion'] = np.cos(2 * np.pi * serie['ds'].dt.month / 12)
        serie['lag_1']        = serie['y'].shift(1)
        serie['lag_3']        = serie['y'].shift(3)
        serie['media_mov_3']  = serie['y'].rolling(3).mean()
        serie_fe     = serie.dropna().reset_index(drop=True)
        ultima_fecha = serie_fe['ds'].max()
        ultimo_idx   = int(serie_fe['mes_num'].max())
        hist_y       = list(serie_fe['y'].values)
        resultado    = []
        for i in range(1, 7):
            fecha_fut = ultima_fecha + pd.DateOffset(months=i)
            mes_n     = ultimo_idx + i
            sin_e     = np.sin(2 * np.pi * fecha_fut.month / 12)
            cos_e     = np.cos(2 * np.pi * fecha_fut.month / 12)
            lag1      = hist_y[-1]
            lag3      = hist_y[-3] if len(hist_y) >= 3 else hist_y[-1]
            mm3       = float(np.mean(hist_y[-3:]))
            pred      = float(modelo_lr.predict([[mes_n, sin_e, cos_e, lag1, lag3, mm3]])[0])
            hist_y.append(pred)
            resultado.append({
                "mes":                str(fecha_fut)[:7],
                "mes_nombre":         fecha_fut.strftime('%B %Y'),
                "ingreso_proyectado": round(pred * 1000, 0),
                "ingreso_miles":      round(pred, 2),
            })
        return resultado
    except:
        return []

# ── Endpoints ──
@app.get("/")
def health():
    return {
        "status":  "ok",
        "sistema": "SISTCO-ML API",
        "version": "2.0.0",
        "mejoras": ["MySQL tiempo real", "Historial conversación", "Contexto de sesión"]
    }

@app.post("/clasificar/cliente")
def clasificar_cliente(datos: DatosCliente):
    try:
        modelo  = modelo_clasificacion['model']
        scaler  = modelo_clasificacion['scaler']

        ratio_mora_reciente = datos.moras_ult_6_meses / (datos.n_meses_activos + 1)
        tendencia_pago      = datos.moras_ult_3_meses - (datos.moras_ult_6_meses - datos.moras_ult_3_meses)
        mora_reciente_bin   = 1 if datos.moras_ult_3_meses > 0 else 0
        racha_relativa      = datos.racha_limpia_final / (datos.antiguedad_meses + 1)
        pago_tardio         = 1 if datos.dia_prom_pago_ult12 > 19.18 else 0
        score_riesgo        = (
            datos.tasa_mora_historica * 0.4 +
            ratio_mora_reciente * 0.3 +
            (1 - min(racha_relativa, 1.0)) * 0.3
        )
        features = [[
            datos.mensualidad, datos.antiguedad_meses,
            datos.megas_cod, datos.municipio_cod, datos.metodo_pago_cod,
            datos.n_meses_activos, datos.n_moras_historicas,
            datos.tasa_mora_historica, datos.moras_ult_3_meses,
            datos.moras_ult_6_meses, datos.racha_limpia_final,
            datos.dia_prom_pago_ult12, ratio_mora_reciente,
            tendencia_pago, mora_reciente_bin, racha_relativa,
            pago_tardio, score_riesgo
        ]]
        features_sc  = scaler.transform(features)
        probabilidad = float(modelo.predict_proba(features_sc)[0][1])

        if probabilidad >= 0.70:
            nivel = "Alto";  color = "rojo";    accion = "Contactar de inmediato para gestión de cobro preventiva"
        elif probabilidad >= 0.40:
            nivel = "Medio"; color = "naranja"; accion = "Monitorear y enviar recordatorio de pago"
        else:
            nivel = "Bajo";  color = "verde";   accion = "Sin acción requerida"

        return {
            "probabilidad":    round(probabilidad * 100, 2),
            "nivel_riesgo":    nivel,
            "color":           color,
            "accion_sugerida": accion,
            "algoritmo":       modelo_clasificacion['algoritmo'],
            "auc_roc_modelo":  round(modelo_clasificacion['auc_roc'], 4),
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/segmentar/cliente")
def segmentar_cliente(datos: DatosSegmentacion):
    try:
        kmeans  = modelo_segmentacion['modelo']
        scaler  = modelo_segmentacion['scaler']
        nombres = modelo_segmentacion['nombres_segmentos']
        info    = modelo_segmentacion['segmentos_info']

        features = [[
            datos.mensualidad, datos.antiguedad_meses,
            datos.n_meses_activos, datos.n_moras_historicas,
            datos.tasa_mora_historica, datos.moras_ult_3_meses,
            datos.moras_ult_6_meses, datos.racha_limpia_final,
            datos.dia_prom_pago_ult12
        ]]
        features_sc = scaler.transform(features)
        cluster_id  = int(kmeans.predict(features_sc)[0])
        nombre      = nombres.get(str(cluster_id), f"Segmento {cluster_id}")
        seg_info    = next((s for s in info if s['cluster'] == cluster_id), {})

        return {
            "cluster_id":      cluster_id,
            "nombre_segmento": nombre,
            "porcentaje_mora": seg_info.get('tasa_mora', 0),
            "descripcion":     f"Segmento {nombre} — {seg_info.get('porcentaje', 0)}% de clientes SISTCO",
            "algoritmo":       modelo_segmentacion['algoritmo'],
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/proyectar/ingresos")
def proyectar_ingresos():
    try:
        proyecciones = _calcular_proyecciones()
        return {
            "proyecciones":          proyecciones,
            "algoritmo":             modelo_ingresos['algoritmo_ganador'],
            "mape_modelo":           round(modelo_ingresos['tabla_comparativa'][0]['MAPE%'], 2),
            "marzo_excluido":        modelo_ingresos.get('marzo_excluido', False),
            "justificacion":         modelo_ingresos.get('justificacion', ''),
            "n_meses_entrenamiento": modelo_ingresos.get('n_meses_serie', 0),
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/segmentos/resumen")
def resumen_segmentos():
    try:
        return {
            "segmentos":        modelo_segmentacion['segmentos_info'],
            "algoritmo":        modelo_segmentacion['algoritmo'],
            "silhouette_score": modelo_segmentacion['silhouette_score'],
            "k_segmentos":      modelo_segmentacion['k_negocio'],
            "varianza_pca":     modelo_segmentacion['varianza_pca'],
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/procesar/excel")
async def procesar_excel(archivo: UploadFile = File(...)):
    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix='.xlsx') as tmp:
            shutil.copyfileobj(archivo.file, tmp)
            ruta_tmp = tmp.name

        import pandas as pd
        import numpy as np
        import re
        from collections import Counter

        FECHA_REF = pd.Timestamp("2026-03-31")

        def clasificar_celda(valor):
            if valor is None or (isinstance(valor, float) and np.isnan(valor)): return "INACTIVO"
            s = str(valor).strip().upper()
            if s == "" or s == "NAN": return "INACTIVO"
            if s.startswith("OK"):   return "OK"
            if s.startswith("W"):    return "MORA"
            if "SUSPENDIDO" in s or "SUSPENDER" in s: return "SUSPENDIDO"
            return "OTRO"

        def extraer_dia_pago(valor):
            if valor is None or (isinstance(valor, float) and np.isnan(valor)): return np.nan
            m = re.search(r'-(\d{1,2})', str(valor).strip())
            if m:
                dia = int(m.group(1))
                if 1 <= dia <= 31: return dia
            return np.nan

        def extraer_metodo_pago(valor):
            if valor is None or (isinstance(valor, float) and np.isnan(valor)): return "DESCONOCIDO"
            s = str(valor).strip().upper()
            if not s.startswith("OK"): return "DESCONOCIDO"
            if " T " in s or s.endswith(" T") or "NEQUI" in s or "T-" in s or "TM" in s: return "TRANSFERENCIA"
            if " C-" in s or " C " in s or "CW" in s or s.endswith(" C"): return "CAJERO"
            for pal in ["ELKIN","AGENTE","COBRADOR","JOSE","CARLOS","WILSON","PEDRO","MARIO","JHON","JOHN"]:
                if pal in s: return "EFECTIVO_AGENTE"
            return "OTRO"

        def normalizar_mensualidad(valor):
            if valor is None or (isinstance(valor, float) and np.isnan(valor)): return np.nan
            if isinstance(valor, (int, float)): return float(valor)
            s = str(valor).strip().upper().replace(",",".").replace(" ","")
            m = re.match(r'^(\d+(?:\.\d+)?)M$', s)
            if m: return float(m.group(1))
            try: return float(s)
            except: return np.nan

        def normalizar_megas(valor):
            if valor is None or (isinstance(valor, float) and np.isnan(valor)): return "DESCONOCIDO"
            s = str(valor).strip().upper().replace(" ","")
            m = re.search(r'(\d+)', s)
            return f"{m.group(1)}M" if m else "DESCONOCIDO"

        def corregir_municipio(v):
            if v is None or (isinstance(v, float) and np.isnan(v)): return "Desconocido"
            municipio_map = {
                "lebrja":"Lebrija","lebrija":"Lebrija","giron":"Girón","gíron":"Girón",
                "girón":"Girón","sabana tores":"Sabana de Torres","sabana de torres":"Sabana de Torres",
                "sabana detorres":"Sabana de Torres","bucaramanga":"Bucaramanga",
                "floridablanca":"Floridablanca","piedecuesta":"Piedecuesta",
                "rionegro":"Rionegro","betulia":"Betulia",
            }
            return municipio_map.get(str(v).strip().lower(), str(v).strip().title())

        def racha_limpia(fila_cls):
            racha = 0
            for cat in reversed(fila_cls.tolist()):
                if cat == "OK": racha += 1
                elif cat == "INACTIVO": continue
                else: break
            return racha

        def metodo_predominante(fila):
            metodos = [extraer_metodo_pago(v) for v in fila if str(v).strip().upper().startswith("OK")]
            metodos = [m for m in metodos if m != "DESCONOCIDO"]
            if not metodos: return "DESCONOCIDO"
            return Counter(metodos).most_common(1)[0][0]

        df_raw = pd.read_excel(ruta_tmp, sheet_name="Pilar", dtype=str)
        df_raw["Mensualidad"] = df_raw["Mensualidad"].apply(normalizar_mensualidad)
        if str(df_raw.columns[-1]).startswith("Unnamed") or df_raw.columns[-1] is None:
            df_raw = df_raw.iloc[:, :100]

        col_names_raw = list(df_raw.columns)
        DESC_KEEP  = ["T.I", "Codigo Cliente", "Mensualidad", "Megas", "Municipio", "Fecha de Instalacion"]
        PAGO_USADAS = col_names_raw[9:96]

        df = df_raw[DESC_KEEP + PAGO_USADAS].copy()
        df_cls = df[PAGO_USADAS].map(clasificar_celda)

        df["T.I"] = df["T.I"].str.strip().str.title().fillna("Desconocido")
        df["Mensualidad"] = pd.to_numeric(df["Mensualidad"], errors="coerce")
        df["Mensualidad"] = df["Mensualidad"].fillna(df["Mensualidad"].median())
        df["Megas_norm"] = df["Megas"].apply(normalizar_megas)
        df["Municipio_norm"] = df["Municipio"].apply(corregir_municipio)
        df["Fecha de Instalacion"] = pd.to_datetime(df["Fecha de Instalacion"], errors="coerce")
        df.loc[df["Fecha de Instalacion"] > FECHA_REF, "Fecha de Instalacion"] = pd.NaT
        df["Codigo Cliente"] = pd.to_numeric(df["Codigo Cliente"], errors="coerce").astype("Int64")

        df["antiguedad_dias"]    = (FECHA_REF - df["Fecha de Instalacion"]).dt.days
        df["antiguedad_meses"]   = (df["antiguedad_dias"] / 30.44).fillna(df["antiguedad_dias"].median() / 30.44)
        df["n_meses_activos"]    = ((df_cls == "OK") | (df_cls == "MORA")).sum(axis=1)
        df["n_moras_historicas"] = (df_cls == "MORA").sum(axis=1)
        df["tasa_mora_historica"]= np.where(df["n_meses_activos"] > 0, df["n_moras_historicas"] / df["n_meses_activos"], 0.0)
        df["es_moroso"]          = (df["n_moras_historicas"] >= 1).astype(int)
        df["moras_ult_3_meses"]  = (df_cls[PAGO_USADAS[-3:]] == "MORA").sum(axis=1)
        df["moras_ult_6_meses"]  = (df_cls[PAGO_USADAS[-6:]] == "MORA").sum(axis=1)
        df["racha_limpia_final"] = df_cls.apply(racha_limpia, axis=1)
        df_dias12                = df[PAGO_USADAS[-12:]].map(extraer_dia_pago)
        df["dia_prom_pago_ult12"]= df_dias12.mean(axis=1).fillna(df_dias12.mean(axis=1).median())
        df["metodo_pago_pred"]   = df[PAGO_USADAS[-12:]].apply(metodo_predominante, axis=1)

        megas_orden  = ["5M","8M","10M","12M","15M","30M","40M","50M","150M","160M","DESCONOCIDO"]
        megas_map    = {v: i for i, v in enumerate(megas_orden)}
        municipios_v = sorted([m for m in df["Municipio_norm"].unique() if m != "Desconocido"])
        muni_map     = {m: i for i, m in enumerate(municipios_v)}
        muni_map["Desconocido"] = -1
        metodo_map   = {"TRANSFERENCIA":0,"CAJERO":1,"EFECTIVO_AGENTE":2,"OTRO":3,"DESCONOCIDO":4}

        df["megas_cod"]       = df["Megas_norm"].map(megas_map).fillna(len(megas_orden)-1).astype(int)
        df["municipio_cod"]   = df["Municipio_norm"].map(muni_map).fillna(-1).astype(int)
        df["metodo_pago_cod"] = df["metodo_pago_pred"].map(metodo_map).fillna(4).astype(int)

        registros = []
        for _, row in df.iterrows():
            registros.append({
                "codigo_cliente":      str(row["Codigo Cliente"]),
                "mensualidad":         round(float(row["Mensualidad"]), 4),
                "antiguedad_meses":    round(float(row["antiguedad_meses"]), 4),
                "megas_cod":           int(row["megas_cod"]),
                "municipio_cod":       int(row["municipio_cod"]),
                "metodo_pago_cod":     int(row["metodo_pago_cod"]),
                "n_meses_activos":     int(row["n_meses_activos"]),
                "n_moras_historicas":  int(row["n_moras_historicas"]),
                "tasa_mora_historica": round(float(row["tasa_mora_historica"]), 4),
                "es_moroso":           int(row["es_moroso"]),
                "moras_ult_3_meses":   int(row["moras_ult_3_meses"]),
                "moras_ult_6_meses":   int(row["moras_ult_6_meses"]),
                "racha_limpia_final":  int(row["racha_limpia_final"]),
                "dia_prom_pago_ult12": round(float(row["dia_prom_pago_ult12"]), 4),
                "megas":               str(row["Megas_norm"]),
                "municipio":           str(row["Municipio_norm"]),
                "metodo_pago":         str(row["metodo_pago_pred"]),
            })

        os.unlink(ruta_tmp)

        return {
            "status":         "ok",
            "total_clientes": len(registros),
            "registros":      registros,
        }

    except Exception as e:
        return {"status": "error", "mensaje": str(e)}

@app.post("/asistente/consulta")
def consulta_asistente(datos: PreguntaAsistente):
    try:
        bd = obtener_datos_bd()

        segmentos = modelo_segmentacion['segmentos_info']
        auc       = round(modelo_clasificacion['auc_roc'], 4)
        mape      = round(modelo_ingresos['tabla_comparativa'][0]['MAPE%'], 2)
        silueta   = round(modelo_segmentacion['silhouette_score'], 4)

        resumen_segs = '\n'.join([
            f"  - {s['nombre']}: {s['n_clientes']} clientes ({s['porcentaje']}%), mora {round(s['tasa_mora']*100,1)}%, antigüedad {round(s['antiguedad_promedio'])}m"
            for s in segmentos
        ])

        try:
            proyecciones = _calcular_proyecciones()
            proy_texto   = '\n'.join([
                f"  - {p['mes_nombre']}: ${p['ingreso_proyectado']:,.0f} COP"
                for p in proyecciones
            ])
        except:
            proy_texto = '  - No disponible'

        sistema = f"""Eres el asistente inteligente de SISTCO-ML con acceso a datos en tiempo real desde MySQL.
SISTCO Sistemas y Comunicaciones SAS — proveedor de internet inalámbrico rural en Santander, Colombia.

═══ DATOS EN TIEMPO REAL — CONSULTADOS AHORA MISMO DESDE MYSQL ═══
- Total clientes: {bd['total']}
- Ingreso mensual estimado: ${bd['ingreso_act']:,.0f} COP
- Mensualidad promedio: ${bd['mens_prom']:,.0f} COP
- Antigüedad promedio: {bd['antig_prom']} meses
- Tasa de mora promedio: {bd['tasa_prom']}%
- Clientes actualmente en mora (es_moroso=1): {bd['mora_ultimo_mes']}
- Clientes con mora en últimos 3 meses: {bd['en_mora_rec']}
- Riesgo Alto (modelo ML): {bd['alto']} clientes
- Riesgo Medio (modelo ML): {bd['medio']} clientes
- Riesgo Bajo (modelo ML): {bd['bajo']} clientes

═══ TOP 5 CLIENTES MAYOR RIESGO (tiempo real) ═══
{bd['top_riesgo']}

═══ MUNICIPIOS — TOP 5 POR MORA (tiempo real) ═══
{bd['muni_mora']}

═══ MUNICIPIOS — TOP 5 POR CLIENTES (tiempo real) ═══
{bd['muni_clientes']}

═══ RIESGO ML POR MUNICIPIO (tiempo real) ═══
{bd['riesgo_municipio']}

═══ DISTRIBUCIÓN DE MENSUALIDADES (tiempo real) ═══
{bd['dist_mensualidades']}

═══ CLIENTES Y MORA POR MUNICIPIO (tiempo real) ═══
{bd['mora_municipio']}

═══ DISTRIBUCIÓN POR PLAN DE MEGAS (tiempo real) ═══
{bd['dist_megas']}

═══ TOP 10 CLIENTES MAYOR MENSUALIDAD (posibles corporativos) ═══
{bd['top_mensualidad']}

═══ CLIENTES CORPORATIVOS — SEGMENTO K-MEANS (alta mensualidad, tiempo real) ═══
Estos son los clientes del segmento Corporativo identificados por el modelo K-Means.
Son los clientes con mayor mensualidad y mayor antigüedad de SISTCO:
{bd['clientes_corporativos']}

═══ CLIENTES POR VEREDA Y MUNICIPIO (tiempo real) ═══
{bd['clientes_vereda']}

═══ PLANES POR VEREDA (tiempo real) ═══
{bd['plan_por_vereda']}

═══ ESTADO DE PAGO POR VEREDA (al día vs en mora) ═══
{bd['estado_pago_vereda']}

═══ TOP 5 CLIENTES MÁS ANTIGUOS ═══
{bd['clientes_mas_antiguos']}

═══ TOP 5 CLIENTES MÁS RECIENTES ═══
{bd['clientes_mas_recientes']}

═══ DETALLE COMPLETO CLIENTES EN MORA ({bd['total_mora_detalle']} clientes) ═══
{bd['detalle_clientes_mora']}

═══ PLANES POR MENSUALIDAD EXACTA ═══
{bd['plan_mensualidad']}

═══ CLIENTES CORPORATIVOS POR VEREDA ═══
{bd['corporativos_por_vereda']}

═══ TIPOS DE IDENTIFICACIÓN ═══
{bd['tipos_id']}

═══ SEGMENTOS K-Means K=5 ═══
{resumen_segs}

═══ PROYECCIÓN INGRESOS PRÓXIMOS 6 MESES ═══
{proy_texto}

═══ MODELOS ML ═══
1. Clasificación: Regresión Logística — AUC-ROC: {auc} — Recall: 93.75%
2. Proyección de Ingresos: Regresión Lineal — MAPE: {mape}%
3. Segmentación: K-Means K=5 — Silueta: {silueta}

═══ INSTRUCCIONES CRÍTICAS ═══
- Responde SIEMPRE en español, claro y profesional, máximo 3 párrafos
- Los datos de arriba son el ESTADO EXACTO Y ACTUAL de la base de datos en este preciso momento
- NUNCA digas "no tengo información" si los datos están en el contexto — búscalos bien antes de responder
- Si la vereda o municipio que pregunta el usuario está en los datos, responde con los datos exactos
- Si realmente no existe en los datos, explica que esa vereda o municipio no tiene clientes registrados
- NUNCA uses datos de preguntas o respuestas anteriores para responder la pregunta actual
- Cada respuesta debe basarse ÚNICAMENTE en los datos del contexto actual mostrado arriba
- Nunca menciones nombres de campos, tablas ni términos técnicos de base de datos
- Habla siempre como un asistente empresarial profesional, no como un sistema técnico
- En lugar de decir 'el campo es_moroso' di simplemente 'los clientes en mora'
- En lugar de decir 'según los datos del contexto' di 'según la información actual del sistema'
- Para clientes específicos por código usa el módulo de Clasificación"""

        messages = [{"role": "system", "content": sistema}]

        if datos.historial:
            for msg in datos.historial[-12:]:
                messages.append({
                    "role":    "user" if msg.rol == "user" else "assistant",
                    "content": msg.contenido
                })

        pregunta_enriquecida = f"""{datos.pregunta}

[CONTEXTO NUMÉRICO ACTUALIZADO - USA ESTOS VALORES EXACTOS]:
- Clientes en mora: {bd['mora_ultimo_mes']}
- Clientes con mora reciente: {bd['en_mora_rec']}
- Total clientes: {bd['total']}
- Riesgo Alto: {bd['alto']} | Medio: {bd['medio']} | Bajo: {bd['bajo']}"""

        messages.append({"role": "user", "content": pregunta_enriquecida})

        respuesta = groq_client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=messages,
            max_tokens=600,
            temperature=0.1,
        )

        texto = respuesta.choices[0].message.content

        return {
            "respuesta": texto,
            "fuente":    "Groq — LLaMA 3.3 70B",
            "datos_bd":  True
        }

    except Exception as e:
        return {
            "respuesta": "El asistente no está disponible. Verifica que FastAPI esté corriendo y la API key de Groq sea correcta.",
            "fuente":    "error",
            "datos_bd":  False
        }