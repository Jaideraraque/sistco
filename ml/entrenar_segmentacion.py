import pandas as pd
import numpy as np
from sklearn.cluster import KMeans, DBSCAN, AgglomerativeClustering
from sklearn.preprocessing import StandardScaler
from sklearn.decomposition import PCA
from sklearn.metrics import silhouette_score, davies_bouldin_score
import joblib
import os
import warnings
warnings.filterwarnings('ignore')

print("=" * 65)
print("SISTCO-ML — Segmentación de Clientes")
print("=" * 65)

# ── 1. Cargar dataset ──
ruta = os.path.join(os.path.dirname(__file__), '..', 'database', 'seeders', 'data', 'dataset_limpio_SISTCO.csv')
df = pd.read_csv(ruta)
print(f"\n✅ Dataset cargado: {df.shape[0]} clientes")

# ── 2. Features para clustering ──
features = [
    'Mensualidad',
    'antiguedad_meses',
    'n_meses_activos',
    'n_moras_historicas',
    'tasa_mora_historica',
    'moras_ult_3_meses',
    'moras_ult_6_meses',
    'racha_limpia_final',
    'dia_prom_pago_ult12',
]

X = df[features].fillna(0)
scaler  = StandardScaler()
X_sc    = scaler.fit_transform(X)
print(f"   Features: {len(features)}")

# ── 3. Evaluación de K óptimo estadístico ──
print("\n🔍 Evaluando K óptimo (2-9)...")
siluetas = {}
for k in range(2, 10):
    km  = KMeans(n_clusters=k, random_state=42, n_init=10)
    lbl = km.fit_predict(X_sc)
    sil = silhouette_score(X_sc, lbl)
    siluetas[k] = sil
    print(f"   K={k}: Silueta={sil:.4f}")

k_estadistico = max(siluetas, key=siluetas.get)
print(f"\n   K óptimo estadístico: {k_estadistico} "
      f"(Silueta={siluetas[k_estadistico]:.4f})")

# ── 4. Decisión de negocio: K=5 ──
# K=2 es el óptimo estadístico pero produce solo 2 grupos
# sin utilidad operativa para SISTCO. K=5 ofrece el mejor
# equilibrio entre separación estadística y perfiles accionables
# para el área administrativa (Silueta=0.4285 > umbral ERS 0.40).
K_FINAL = 5
print(f"\n✅ K seleccionado para producción: {K_FINAL}")
print(f"   Justificación: K={K_FINAL} (Silueta={siluetas[K_FINAL]:.4f}) ofrece")
print(f"   el mejor equilibrio entre validez estadística (≥0.40 ERS)")
print(f"   y utilidad operativa para SISTCO (5 perfiles accionables).")
print(f"   K={k_estadistico} produce segmentos insuficientes para")
print(f"   estrategias comerciales diferenciadas.")

# ── 5. Comparar algoritmos con K=5 ──
print("\n" + "=" * 65)
print("COMPARANDO ALGORITMOS DE SEGMENTACIÓN (K=5)")
print("=" * 65)

resultados = []

# K-Means K=5
print(f"\n🔄 K-Means (K={K_FINAL})...")
km5        = KMeans(n_clusters=K_FINAL, random_state=42, n_init=10)
labels_km  = km5.fit_predict(X_sc)
sil_km     = silhouette_score(X_sc, labels_km)
db_km      = davies_bouldin_score(X_sc, labels_km)
resultados.append({
    'Algoritmo': f'K-Means (K={K_FINAL})',
    'Silueta': sil_km,
    'Davies-Bouldin': db_km,
    'N Clusters': K_FINAL
})
print(f"   Silueta: {sil_km:.4f} | Davies-Bouldin: {db_km:.4f}")

# DBSCAN
print(f"\n🔄 DBSCAN...")
dbscan    = DBSCAN(eps=1.2, min_samples=5)
labels_db = dbscan.fit_predict(X_sc)
n_cl_db   = len(set(labels_db)) - (1 if -1 in labels_db else 0)
n_ruido   = (labels_db == -1).sum()
if n_cl_db > 1:
    mask_db = labels_db != -1
    sil_db  = silhouette_score(X_sc[mask_db], labels_db[mask_db])
    db_db   = davies_bouldin_score(X_sc[mask_db], labels_db[mask_db])
else:
    sil_db, db_db = 0.0, 99.0
resultados.append({
    'Algoritmo': 'DBSCAN',
    'Silueta': sil_db,
    'Davies-Bouldin': db_db,
    'N Clusters': n_cl_db
})
print(f"   Clusters: {n_cl_db} | Ruido: {n_ruido} puntos")
print(f"   Silueta: {sil_db:.4f} | Davies-Bouldin: {db_db:.4f}")

# Clustering Jerárquico K=5
print(f"\n🔄 Clustering Jerárquico (K={K_FINAL})...")
agg        = AgglomerativeClustering(n_clusters=K_FINAL)
labels_agg = agg.fit_predict(X_sc)
sil_agg    = silhouette_score(X_sc, labels_agg)
db_agg     = davies_bouldin_score(X_sc, labels_agg)
resultados.append({
    'Algoritmo': f'Clustering Jerárquico (K={K_FINAL})',
    'Silueta': sil_agg,
    'Davies-Bouldin': db_agg,
    'N Clusters': K_FINAL
})
print(f"   Silueta: {sil_agg:.4f} | Davies-Bouldin: {db_agg:.4f}")

# ── 6. Tabla comparativa ──
print("\n" + "=" * 65)
print("TABLA COMPARATIVA — SEGMENTACIÓN")
print("=" * 65)
res_df  = pd.DataFrame(resultados).sort_values('Silueta', ascending=False)
print(res_df.to_string(index=False, float_format=lambda x: f"{x:.4f}"))
ganador = res_df.iloc[0]['Algoritmo']
print(f"\n🏆 ALGORITMO GANADOR: {ganador}")
print(f"   Criterio: mayor Índice de Silueta con K={K_FINAL} segmentos")

# ── 7. Modelo final con ganador ──
# Usar etiquetas del ganador
if 'K-Means' in ganador:
    labels_final = labels_km
    sil_final    = sil_km
    db_final     = db_km
elif 'Jerárquico' in ganador:
    labels_final = labels_agg
    sil_final    = sil_agg
    db_final     = db_agg
else:
    labels_final = labels_km
    sil_final    = sil_km
    db_final     = db_km

# ── 8. Analizar y nombrar segmentos ──
df['cluster'] = labels_final
print(f"\n📊 Características por segmento ({ganador}):")
print(f"   {'Seg':<4} {'Nombre':<22} {'Clientes':>8} {'%':>6} "
      f"{'Mora':>8} {'Mensual':>10} {'Antigüed':>10}")
print(f"   {'-'*72}")

nombres_segmentos = {}
segmentos_info    = []

for c in sorted(df['cluster'].unique()):
    seg       = df[df['cluster'] == c]
    n         = len(seg)
    pct       = n / len(df) * 100
    mora      = seg['tasa_mora_historica'].mean()
    mens      = seg['Mensualidad'].mean()
    antig     = seg['antiguedad_meses'].mean()
    racha     = seg['racha_limpia_final'].mean()
    mora_rec  = seg['moras_ult_3_meses'].mean()

    # Asignar nombre según perfil
    if mora < 0.01 and antig > 40:
        nombre = 'Premium'
    elif mora < 0.05 and antig > 20:
        nombre = 'Estable'
    elif mens > 200:
        nombre = 'Corporativo'
    elif mora > 0.20 or mora_rec > 0.5:
        nombre = 'Alto Riesgo'
    elif mora > 0.05 or mora_rec > 0.1:
        nombre = 'En Riesgo'
    elif antig < 12:
        nombre = 'Nuevo'
    else:
        nombre = 'Intermedio'

    nombres_segmentos[str(c)] = nombre

    print(f"   {c:<4} {nombre:<22} {n:>8} {pct:>5.1f}% "
          f"{mora*100:>7.2f}% ${mens:>9,.0f} {antig:>9.1f}m")

    segmentos_info.append({
        'cluster': int(c),
        'nombre': nombre,
        'n_clientes': int(n),
        'porcentaje': round(pct, 1),
        'tasa_mora': round(mora, 4),
        'mensualidad_promedio': round(mens, 0),
        'antiguedad_promedio': round(antig, 1),
        'racha_limpia': round(racha, 1),
        'mora_reciente': round(mora_rec, 2),
    })

# ── 9. Verificar ERS ──
print(f"\n✅ Verificación ERS:")
print(f"   Índice de Silueta ≥ 0.40: "
      f"{'✅ CUMPLE' if sil_final >= 0.40 else '⚠️ REVISAR'} "
      f"({sil_final:.4f})")
print(f"   Davies-Bouldin (referencia): {db_final:.4f}")

# ── 10. PCA 2D ──
pca    = PCA(n_components=2, random_state=42)
X_pca  = pca.fit_transform(X_sc)
var_ex = pca.explained_variance_ratio_.sum() * 100
print(f"   PCA 2D varianza explicada: {var_ex:.1f}%")

# ── 11. Guardar ──
os.makedirs(os.path.join(os.path.dirname(__file__), 'modelos'), exist_ok=True)
ruta_modelo = os.path.join(os.path.dirname(__file__),
                            'modelos', 'modelo_segmentacion.pkl')

joblib.dump({
    'modelo':              km5,
    'scaler':              scaler,
    'pca':                 pca,
    'features':            features,
    'algoritmo':           ganador,
    'k_optimo_estadistico': k_estadistico,
    'k_negocio':           K_FINAL,
    'silhouette_score':    sil_final,
    'davies_bouldin':      db_final,
    'nombres_segmentos':   nombres_segmentos,
    'segmentos_info':      segmentos_info,
    'tabla_comparativa':   res_df.to_dict('records'),
    'coords_pca':          X_pca.tolist(),
    'labels':              labels_final.tolist(),
    'varianza_pca':        var_ex,
}, ruta_modelo)

print(f"\n💾 Modelo guardado: {ruta_modelo}")
print(f"\n{'='*65}")
print("¡Segmentación completada!")
print(f"{'='*65}")