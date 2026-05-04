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
import threading
import time

warnings.filterwarnings('ignore')

# ── Groq client ──
groq_client = Groq(api_key=os.environ.get("GROQ_API_KEY"))

# ── Configuración MySQL ──
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
print("🚀 API lista en http://localhost:8000")
print(f"🔗 Conectado a DB: {os.environ.get('DB_HOST', '127.0.0.1')}:{os.environ.get('DB_PORT', 3306)}/{os.environ.get('DB_DATABASE', 'sistco')}")

# ── Cache de ubicaciones ──
_cache_ubicaciones = {"veredas": set(), "municipios": set(), "ultimo_refresh": 0}

def _cargar_cache_ubicaciones():
    try:
        conn = get_db()
        with conn.cursor() as cursor:
            cursor.execute("SELECT DISTINCT vereda FROM clientes WHERE vereda IS NOT NULL")
            veredas = {r['vereda'].lower().strip() for r in cursor.fetchall()}
            cursor.execute("SELECT DISTINCT municipio FROM clientes WHERE municipio IS NOT NULL")
            municipios = {r['municipio'].lower().strip() for r in cursor.fetchall()}
        conn.close()
        _cache_ubicaciones["veredas"]        = veredas
        _cache_ubicaciones["municipios"]     = municipios
        _cache_ubicaciones["ultimo_refresh"] = time.time()
        print(f"✅ Cache ubicaciones: {len(veredas)} veredas, {len(municipios)} municipios")
    except Exception as e:
        print(f"⚠️  Cache ubicaciones error: {e}")

def _refresh_cache_loop():
    while True:
        time.sleep(1800)  # 30 minutos
        _cargar_cache_ubicaciones()

# Cargar cache al arrancar
_cargar_cache_ubicaciones()
threading.Thread(target=_refresh_cache_loop, daemon=True).start()

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

            cursor.execute("""
                SELECT nivel_riesgo_ml, COUNT(*) as n
                FROM clientes WHERE nivel_riesgo_ml IS NOT NULL
                GROUP BY nivel_riesgo_ml
            """)
            riesgos = {r['nivel_riesgo_ml']: r['n'] for r in cursor.fetchall()}
            alto  = riesgos.get('Alto',  0)
            medio = riesgos.get('Medio', 0)
            bajo  = riesgos.get('Bajo',  0)

            cursor.execute("""
                SELECT municipio, ROUND(AVG(tasa_mora_historica)*100,2) as mora_prom
                FROM clientes WHERE municipio IS NOT NULL
                GROUP BY municipio ORDER BY mora_prom DESC LIMIT 5
            """)
            muni_mora = '\n'.join([f"  - {r['municipio']}: {r['mora_prom']}%" for r in cursor.fetchall()])

            cursor.execute("""
                SELECT municipio, COUNT(*) as total FROM clientes
                WHERE municipio IS NOT NULL GROUP BY municipio ORDER BY total DESC LIMIT 5
            """)
            muni_clientes = '\n'.join([f"  - {r['municipio']}: {r['total']} clientes" for r in cursor.fetchall()])

            cursor.execute("SELECT COUNT(*) as n FROM clientes WHERE es_moroso = 1")
            mora_ultimo_mes = cursor.fetchone()['n']

            cursor.execute("""
                SELECT codigo_cliente, municipio, ROUND(probabilidad_ml,2) as prob,
                       nivel_riesgo_ml, ROUND(tasa_mora_historica*100,1) as mora_hist
                FROM clientes WHERE probabilidad_ml IS NOT NULL
                ORDER BY probabilidad_ml DESC LIMIT 5
            """)
            top_riesgo_str = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}): {r['prob']}% probabilidad, mora histórica {r['mora_hist']}%"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT municipio,
                       SUM(CASE WHEN nivel_riesgo_ml='Alto'  THEN 1 ELSE 0 END) as alto,
                       SUM(CASE WHEN nivel_riesgo_ml='Medio' THEN 1 ELSE 0 END) as medio,
                       SUM(CASE WHEN nivel_riesgo_ml='Bajo'  THEN 1 ELSE 0 END) as bajo,
                       COUNT(*) as total
                FROM clientes WHERE nivel_riesgo_ml IS NOT NULL
                GROUP BY municipio ORDER BY alto DESC
            """)
            riesgo_municipio = '\n'.join([
                f"  - {r['municipio']}: Alto={r['alto']}, Medio={r['medio']}, Bajo={r['bajo']} (total {r['total']})"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT ROUND(mensualidad*1000,0) as valor, COUNT(*) as total
                FROM clientes GROUP BY valor ORDER BY total DESC LIMIT 10
            """)
            dist_mensualidades = '\n'.join([
                f"  - ${int(r['valor']):,} COP: {r['total']} clientes" for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT municipio, COUNT(*) as total, SUM(es_moroso) as en_mora
                FROM clientes GROUP BY municipio ORDER BY total DESC
            """)
            mora_municipio = '\n'.join([
                f"  - {r['municipio']}: {r['total']} clientes, {r['en_mora']} en mora"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT megas, COUNT(*) as total, ROUND(AVG(mensualidad*1000),0) as mensualidad_prom
                FROM clientes GROUP BY megas ORDER BY total DESC
            """)
            dist_megas = '\n'.join([
                f"  - {r['megas']}: {r['total']} clientes, mensualidad promedio ${int(r['mensualidad_prom']):,} COP"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT codigo_cliente, municipio, megas, ROUND(mensualidad*1000,0) as mensualidad, nivel_riesgo_ml
                FROM clientes ORDER BY mensualidad DESC LIMIT 10
            """)
            top_mensualidad = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}): {r['megas']}, ${int(r['mensualidad']):,} COP, riesgo {r['nivel_riesgo_ml'] or 'N/A'}"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT codigo_cliente, municipio, megas,
                       ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad,
                       ROUND(tasa_mora_historica*100,2) as mora_hist, nivel_riesgo_ml
                FROM clientes WHERE mensualidad >= 500
                ORDER BY mensualidad DESC, antiguedad_meses DESC LIMIT 6
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
                           ROUND(mensualidad*1000,0) as mensualidad, ROUND(antiguedad_meses,0) as antiguedad
                    FROM clientes ORDER BY mensualidad DESC LIMIT 4
                """)
                clientes_corporativos = '\n'.join([
                    f"  - Código {r['codigo_cliente']} ({r['municipio']}): plan {r['megas']}, ${int(r['mensualidad']):,} COP mensual, {int(r['antiguedad'])} meses antigüedad"
                    for r in cursor.fetchall()
                ])

            # ── CONSULTAS DE VEREDAS ──

            cursor.execute("""
                SELECT COALESCE(vereda, 'Sin vereda') as vereda, municipio,
                       COUNT(*) as total, SUM(es_moroso) as en_mora,
                       SUM(CASE WHEN es_moroso=0 THEN 1 ELSE 0 END) as al_dia,
                       ROUND(AVG(tasa_mora_historica)*100,2) as mora_prom,
                       SUM(CASE WHEN mensualidad >= 500 THEN 1 ELSE 0 END) as corporativos,
                       SUM(CASE WHEN megas = '5M' THEN 1 ELSE 0 END) as plan_5m,
                       ROUND(MAX(mensualidad*1000),0) as mens_max,
                       GROUP_CONCAT(DISTINCT megas ORDER BY mensualidad DESC SEPARATOR ', ') as planes
                FROM clientes GROUP BY vereda, municipio ORDER BY municipio, total DESC
            """)
            resumen_veredas = '\n'.join([
                f"  - {r['vereda']} ({r['municipio']}): {r['total']} clientes, {r['al_dia']} al día, {r['en_mora']} en mora, mora prom {r['mora_prom']}%, corporativos {r['corporativos']}, plan 5M {r['plan_5m']}, plan más costoso ${int(r['mens_max']):,} COP, planes: {r['planes']}"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT codigo_cliente, municipio, COALESCE(vereda,'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad, fecha_instalacion
                FROM clientes ORDER BY antiguedad_meses DESC LIMIT 5
            """)
            mas_antiguos = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): {int(r['antiguedad'])} meses desde {r['fecha_instalacion']}, {r['megas']}, ${int(r['mensualidad']):,} COP"
                for r in cursor.fetchall()
            ])

            cursor.execute("""
                SELECT codigo_cliente, municipio, COALESCE(vereda,'Sin vereda') as vereda,
                       megas, ROUND(mensualidad*1000,0) as mensualidad,
                       ROUND(antiguedad_meses,0) as antiguedad, fecha_instalacion
                FROM clientes ORDER BY antiguedad_meses ASC LIMIT 5
            """)
            mas_recientes = '\n'.join([
                f"  - Código {r['codigo_cliente']} ({r['municipio']}, {r['vereda']}): instalado {r['fecha_instalacion']}, {r['megas']}, ${int(r['mensualidad']):,} COP"
                for r in cursor.fetchall()
            ])

            cursor.execute("SELECT COUNT(*) as total FROM clientes WHERE ROUND(mensualidad*1000,0) = 80000")
            total_80k = cursor.fetchone()['total']

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
            "resumen_veredas":       resumen_veredas,
            "mas_antiguos":          mas_antiguos,
            "mas_recientes":         mas_recientes,
            "total_80k":             total_80k,
        }

    except Exception as e:
        import traceback
        print(f"Error MySQL: {e}")
        print(traceback.format_exc())
        return {
            "total": 781, "tasa_prom": 2.63, "en_mora_rec": 249,
            "ingreso_act": 81158800, "mens_prom": 103917, "antig_prom": 31.5,
            "alto": 28, "medio": 145, "bajo": 608,
            "muni_mora": "  - No disponible", "muni_clientes": "  - No disponible",
            "mora_ultimo_mes": 0, "top_riesgo": "  - No disponible",
            "riesgo_municipio": "  - No disponible", "dist_mensualidades": "  - No disponible",
            "mora_municipio": "  - No disponible", "dist_megas": "  - No disponible",
            "top_mensualidad": "  - No disponible", "clientes_corporativos": "  - No disponible",
            "resumen_veredas": "  - No disponible",
            "mas_antiguos": "  - No disponible", "mas_recientes": "  - No disponible",
            "total_80k": 0,
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

# ── Motor de detección de intenciones ──
def detectar_intenciones(pregunta: str) -> set:
    p = pregunta.lower().strip()
    intenciones = set()

    # Estrategia A — Semántica por palabras clave
    palabras_ubicacion   = ["vereda","finca","barrio","corregimiento","sector","parcela","zona"]
    palabras_mora        = ["mora","riesgo","deuda","cobro","atrasado","vencido","incumplimiento","pago"]
    palabras_finanzas    = ["ingreso","facturación","facturacion","mensualidad","proyección","proyeccion","precio","dinero","plata","cobro","revenue"]
    palabras_segmentacion= ["segmento","cluster","grupo","perfil","kmeans","clasificacion","clasificación"]
    palabras_clientes    = ["código","codigo","antiguo","reciente","instalacion","instalación","corporativo","cliente","quien","quién"]
    palabras_megas       = ["mega","megas","plan","servicio","internet","mb","m "]

    if any(p_k in p for p_k in palabras_ubicacion):   intenciones.add("UBICACION")
    if any(p_k in p for p_k in palabras_mora):        intenciones.add("MORA")
    if any(p_k in p for p_k in palabras_finanzas):    intenciones.add("FINANZAS")
    if any(p_k in p for p_k in palabras_segmentacion):intenciones.add("SEGMENTACION")
    if any(p_k in p for p_k in palabras_clientes):    intenciones.add("CLIENTES")
    if any(p_k in p for p_k in palabras_megas):       intenciones.add("MEGAS")

    # Estrategia B — Match dinámico contra cache (normalizado)
    for vereda in _cache_ubicaciones.get("veredas", set()):
        # Match parcial: alguna palabra de la vereda aparece en la pregunta
        partes_vereda = vereda.replace("vereda ", "").split()
        if any(parte in p for parte in partes_vereda if len(parte) > 3):
            intenciones.add("UBICACION")
            break

    for municipio in _cache_ubicaciones.get("municipios", set()):
        if municipio in p:
            intenciones.add("MUNICIPIO")
            break

    # Preguntas especiales
    if any(x in p for x in ["cuantos tienen", "más de 1", "mas de 1", "dos servicio", "doble"]):
        intenciones.add("MULTIPLES")
    if "80000" in p or "80.000" in p or "ochenta" in p:
        intenciones.add("PRECIO_80K")
    if any(x in p for x in ["antiguo", "reciente", "primer", "ultimo", "último", "instaló", "instalo"]):
        intenciones.add("CLIENTES")
    if any(x in p for x in ["cómo vamos", "como vamos", "resumen", "general", "estado"]):
        intenciones.add("GENERAL")

    # Si no detectó nada, asumir GENERAL
    if not intenciones:
        intenciones.add("GENERAL")

    return intenciones

# ── Constructor de system prompt dinámico ──
def construir_system_prompt(intenciones: set, bd: dict, segmentos, auc: float, mape: float, silueta: float, pregunta: str = "") -> str:

    # Estimador simple de tokens (1 token ≈ 4 caracteres)
    def est_tokens(texto): return len(texto) // 4

    # BLOQUE BASE — siempre presente
    base = f"""Eres el asistente inteligente de SISTCO-ML. SISTCO Sistemas y Comunicaciones SAS — proveedor de internet inalámbrico rural en Santander, Colombia.

═══ DATOS BASE ═══
- Total clientes: {bd['total']} | Ingreso mensual: ${bd['ingreso_act']:,.0f} COP
- Mensualidad promedio: ${bd['mens_prom']:,.0f} COP | Antigüedad promedio: {bd['antig_prom']} meses
- Tasa mora promedio: {bd['tasa_prom']}% | En mora ahora: {bd['mora_ultimo_mes']} | Mora reciente: {bd['en_mora_rec']}
- Riesgo Alto: {bd['alto']} | Medio: {bd['medio']} | Bajo: {bd['bajo']}
- Clientes con servicio $80,000: {bd['total_80k']}
- Clientes con más de un servicio: ninguno (un servicio por cliente)"""

    bloques = [base]
    tokens_actuales = est_tokens(base)
    HARD_CAP = 9000

    # Función para agregar bloque si cabe
    def agregar(bloque):
        nonlocal tokens_actuales
        t = est_tokens(bloque)
        if tokens_actuales + t < HARD_CAP:
            bloques.append(bloque)
            tokens_actuales += t
            return True
        return False

    # BLOQUE UBICACION — veredas con filtrado por pregunta
    if "UBICACION" in intenciones or "MUNICIPIO" in intenciones:
        p_norm = pregunta.lower()
        
        # Si pregunta "todas las veredas" mostrar resumen compacto
        if any(x in p_norm for x in ["todas", "cada vereda", "por vereda", "alfabético", "alfabetico", "todas las veredas", "lista de veredas"]):
            agregar(f"""
═══ RESUMEN POR VEREDA (orden alfabético) ═══
{bd['resumen_veredas'][:3000]}""")
        else:
            # Filtrar solo las veredas mencionadas en la pregunta
            lineas_relevantes = []
            for linea in bd['resumen_veredas'].split('\n'):
                linea_norm = linea.lower()
                # Incluir si alguna palabra de la línea aparece en la pregunta
                palabras_linea = [w for w in linea_norm.split() if len(w) > 3]
                if any(w in p_norm for w in palabras_linea):
                    lineas_relevantes.append(linea)
            
            # Si no encontró nada específico, mostrar todas pero resumidas
            if not lineas_relevantes:
                vereda_data = bd['resumen_veredas'][:1000]
            else:
                vereda_data = '\n'.join(lineas_relevantes)
            
            agregar(f"""
═══ DATOS DE VEREDAS RELEVANTES ═══
{vereda_data}

═══ RESUMEN COMPLETO POR VEREDA ═══
{bd['resumen_veredas'][:1000]}""")

    # BLOQUE MORA
    if "MORA" in intenciones:
        agregar(f"""
═══ MUNICIPIOS TOP 5 POR MORA ═══
{bd['muni_mora']}
═══ RIESGO ML POR MUNICIPIO ═══
{bd['riesgo_municipio']}
═══ TOP 5 CLIENTES MAYOR RIESGO ═══
{bd['top_riesgo']}""")

    # BLOQUE MUNICIPIO
    if "MUNICIPIO" in intenciones:
        agregar(f"""
═══ CLIENTES Y MORA POR MUNICIPIO ═══
{bd['mora_municipio']}
═══ MUNICIPIOS TOP 5 POR CLIENTES ═══
{bd['muni_clientes']}""")

    # BLOQUE FINANZAS
    if "FINANZAS" in intenciones:
        try:
            proyecciones = _calcular_proyecciones()
            proy = '\n'.join([f"  - {p['mes_nombre']}: ${p['ingreso_proyectado']:,.0f} COP" for p in proyecciones])
        except:
            proy = "  - No disponible"
        agregar(f"""
═══ DISTRIBUCIÓN DE MENSUALIDADES ═══
{bd['dist_mensualidades']}
═══ PROYECCIÓN INGRESOS 6 MESES ═══
{proy}
═══ MODELOS ML ═══
Clasificación: AUC-ROC {auc} | Proyección: MAPE {mape}% | Segmentación: Silueta {silueta}""")

    # BLOQUE MEGAS / PLANES
    if "MEGAS" in intenciones:
        agregar(f"""
═══ DISTRIBUCIÓN POR PLAN DE MEGAS ═══
{bd['dist_megas']}
═══ PLANES POR MENSUALIDAD EXACTA ═══
{bd['dist_mensualidades']}""")

    # BLOQUE CLIENTES
    if "CLIENTES" in intenciones:
        agregar(f"""
═══ TOP 5 CLIENTES MÁS ANTIGUOS ═══
{bd['mas_antiguos']}
═══ TOP 5 CLIENTES MÁS RECIENTES ═══
{bd['mas_recientes']}
═══ TOP 10 MAYOR MENSUALIDAD ═══
{bd['top_mensualidad']}
═══ CLIENTES CORPORATIVOS ═══
{bd['clientes_corporativos']}""")

    # BLOQUE SEGMENTACION
    if "SEGMENTACION" in intenciones:
        resumen_segs = '\n'.join([
            f"  - {s['nombre']}: {s['n_clientes']} clientes ({s['porcentaje']}%), mora {round(s['tasa_mora']*100,1)}%, antigüedad {round(s['antiguedad_promedio'])}m"
            for s in segmentos
        ])
        agregar(f"""
═══ SEGMENTOS K-Means K=5 ═══
{resumen_segs}
Silueta: {silueta} | Modelos ML: AUC-ROC {auc}""")

    # BLOQUE GENERAL — si no hay nada específico o se pidió resumen
    if "GENERAL" in intenciones or len(intenciones) == 0:
        agregar(f"""
═══ MUNICIPIOS TOP 5 POR CLIENTES ═══
{bd['muni_clientes']}
═══ DISTRIBUCIÓN POR PLAN DE MEGAS ═══
{bd['dist_megas']}
═══ CLIENTES CORPORATIVOS ═══
{bd['clientes_corporativos']}
═══ MODELOS ML ═══
Clasificación: AUC-ROC {auc} | Proyección: MAPE {mape}% | Segmentación: Silueta {silueta}""")

    # INSTRUCCIONES — siempre al final
    instrucciones = """
═══ INSTRUCCIONES ═══
- Responde SIEMPRE en español, claro y profesional, máximo 3 párrafos
- Los datos de arriba son EXACTOS y ACTUALES — úsalos directamente
- NUNCA digas "no tengo información" si los datos están aquí — búscalos bien
- Para veredas: busca en RESUMEN POR VEREDA el nombre exacto o similar
- Para plan más costoso en vereda X: busca el mens_max de esa vereda en el resumen
- Para clientes al día en vereda X: busca el campo al_dia de esa vereda
- Para corporativos por vereda: busca el campo corporativos de esa vereda
- Para más de un servicio: ningún cliente tiene más de un servicio actualmente
- Para cliente más antiguo/reciente: usa TOP 5 CLIENTES MÁS ANTIGUOS/RECIENTES
- Nunca menciones nombres de campos técnicos de base de datos
- Habla como asistente empresarial profesional"""

    bloques.append(instrucciones)
    return '\n'.join(bloques)

# ── Endpoints ──
@app.get("/")
def health():
    return {
        "status":  "ok",
        "sistema": "SISTCO-ML API",
        "version": "2.0.0",
        "cache_veredas": len(_cache_ubicaciones.get("veredas", set())),
        "cache_municipios": len(_cache_ubicaciones.get("municipios", set())),
    }

@app.get("/debug")
def debug():
    bd = obtener_datos_bd()
    test_intenciones = detectar_intenciones("cuantos clientes hay en la vereda Lisboa")
    return {
        "tiene_veredas": "resumen_veredas" in bd,
        "muestra_vereda": bd.get("resumen_veredas", "NO EXISTE")[:400],
        "total_80k": bd.get("total_80k", 0),
        "cache_veredas": list(_cache_ubicaciones.get("veredas", set()))[:10],
        "test_intenciones": list(test_intenciones),
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
        DESC_KEEP   = ["T.I", "Codigo Cliente", "Mensualidad", "Megas", "Municipio", "Fecha de Instalacion"]
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
        return {"status": "ok", "total_clientes": len(registros), "registros": registros}

    except Exception as e:
        return {"status": "error", "mensaje": str(e)}

@app.post("/asistente/consulta")
def consulta_asistente(datos: PreguntaAsistente):
    try:
        bd       = obtener_datos_bd()
        auc      = round(modelo_clasificacion['auc_roc'], 4)
        mape     = round(modelo_ingresos['tabla_comparativa'][0]['MAPE%'], 2)
        silueta  = round(modelo_segmentacion['silhouette_score'], 4)
        segmentos = modelo_segmentacion['segmentos_info']

        # Detectar intenciones y construir prompt dinámico
        intenciones = detectar_intenciones(datos.pregunta)
        sistema     = construir_system_prompt(intenciones, bd, segmentos, auc, mape, silueta, datos.pregunta)

        # Historial controlado — máximo 8 mensajes (4 intercambios)
        messages = [{"role": "system", "content": sistema}]
        if datos.historial:
            for msg in datos.historial[-8:]:
                messages.append({
                    "role":    "user" if msg.rol == "user" else "assistant",
                    "content": msg.contenido
                })

        messages.append({"role": "user", "content": datos.pregunta})

        respuesta = groq_client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=messages,
            max_tokens=500,
            temperature=0.1,
        )

        return {
            "respuesta": respuesta.choices[0].message.content,
            "fuente":    "Groq — LLaMA 3.3 70B",
            "datos_bd":  True
        }

    except Exception as e:
        import traceback
        print(f"ERROR ASISTENTE: {e}")
        print(traceback.format_exc())
        return {
            "respuesta": f"Error: {str(e)}",
            "fuente":    "error",
            "datos_bd":  False
        }