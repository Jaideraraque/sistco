import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier, GradientBoostingClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.svm import SVC
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_val_score
from sklearn.metrics import (roc_auc_score, classification_report,
                              precision_score, recall_score, f1_score, accuracy_score)
from sklearn.preprocessing import StandardScaler
from imblearn.over_sampling import SMOTE
import joblib
import os
import warnings
warnings.filterwarnings('ignore')

print("=" * 65)
print("SISTCO-ML — Clasificación de Clientes (Riesgo de Incumplimiento)")
print("=" * 65)

# ── 1. Cargar dataset ──
ruta = os.path.join(os.path.dirname(__file__), '..', 'database', 'seeders', 'data', 'dataset_limpio_SISTCO.csv')
df = pd.read_csv(ruta)
print(f"\n✅ Dataset cargado: {df.shape[0]} clientes, {df.shape[1]} columnas")

# ── 2. Feature Engineering ──
print("\n🔧 Aplicando Feature Engineering...")

df['ratio_mora_reciente'] = df['moras_ult_6_meses'] / (df['n_meses_activos'] + 1)
df['tendencia_pago']      = df['moras_ult_3_meses'] - (df['moras_ult_6_meses'] - df['moras_ult_3_meses'])
df['mora_reciente_bin']   = (df['moras_ult_3_meses'] > 0).astype(int)
df['racha_relativa']      = df['racha_limpia_final'] / (df['antiguedad_meses'] + 1)
df['pago_tardio']         = (df['dia_prom_pago_ult12'] > df['dia_prom_pago_ult12'].quantile(0.75)).astype(int)
df['score_riesgo']        = (
    df['tasa_mora_historica'] * 0.4 +
    df['ratio_mora_reciente'] * 0.3 +
    (1 - df['racha_relativa'].clip(0, 1)) * 0.3
)

print("   ✓ ratio_mora_reciente  — tendencia de mora reciente vs histórica")
print("   ✓ tendencia_pago       — si el cliente está mejorando o empeorando")
print("   ✓ mora_reciente_bin    — tuvo mora en últimos 3 meses (0/1)")
print("   ✓ racha_relativa       — racha limpia relativa a antigüedad")
print("   ✓ pago_tardio          — día de pago por encima del percentil 75")
print("   ✓ score_riesgo         — score compuesto ponderado")

# ── 3. Features y target ──
features = [
    'Mensualidad', 'antiguedad_meses', 'megas_cod', 'municipio_cod',
    'metodo_pago_cod', 'n_meses_activos', 'n_moras_historicas',
    'tasa_mora_historica', 'moras_ult_3_meses', 'moras_ult_6_meses',
    'racha_limpia_final', 'dia_prom_pago_ult12',
    'ratio_mora_reciente', 'tendencia_pago', 'mora_reciente_bin',
    'racha_relativa', 'pago_tardio', 'score_riesgo'
]
target = 'objetivo_mora_ultimo_mes'

X = df[features].fillna(0)
y = df[target]

print(f"\n📊 Distribución de clases:")
print(f"   Al día:          {(y==0).sum()} ({(y==0).mean()*100:.1f}%)")
print(f"   Incumplimiento:  {(y==1).sum()} ({(y==1).mean()*100:.1f}%)")
print(f"   Features totales: {len(features)}")

# ── 4. Split ──
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

# ── 5. SMOTE ──
print(f"\n⚖️  Aplicando SMOTE...")
smote = SMOTE(random_state=42, k_neighbors=min(5, (y_train==1).sum()-1))
X_train_bal, y_train_bal = smote.fit_resample(X_train, y_train)
print(f"   Train balanceado: {X_train_bal.shape[0]} muestras")

# Escalar para LR y SVM
scaler = StandardScaler()
X_train_sc = scaler.fit_transform(X_train_bal)
X_test_sc  = scaler.transform(X_test)

# ── 6. Comparar algoritmos ──
print("\n" + "=" * 65)
print("COMPARANDO ALGORITMOS")
print("=" * 65)

algoritmos = {
    'Regresión Logística': (
        LogisticRegression(class_weight='balanced', max_iter=1000, random_state=42),
        X_train_sc, X_test_sc
    ),
    'Random Forest': (
        RandomForestClassifier(n_estimators=200, max_depth=10, min_samples_split=3,
                               class_weight='balanced', random_state=42, n_jobs=-1),
        X_train_bal, X_test
    ),
    'Gradient Boosting': (
        GradientBoostingClassifier(n_estimators=200, max_depth=4,
                                   learning_rate=0.05, random_state=42),
        X_train_bal, X_test
    ),
    'SVM': (
        SVC(class_weight='balanced', probability=True,
            kernel='rbf', C=1.0, random_state=42),
        X_train_sc, X_test_sc
    ),
}

cv = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
resultados = []

for nombre, (modelo, X_tr, X_te) in algoritmos.items():
    print(f"\n🔄 Entrenando {nombre}...")
    modelo.fit(X_tr, y_train_bal)
    y_prob = modelo.predict_proba(X_te)[:, 1]
    y_pred = modelo.predict(X_te)

    auc  = roc_auc_score(y_test, y_prob)
    acc  = accuracy_score(y_test, y_pred)
    prec = precision_score(y_test, y_pred, zero_division=0)
    rec  = recall_score(y_test, y_pred, zero_division=0)
    f1   = f1_score(y_test, y_pred, zero_division=0)

    X_cv = X_test_sc if nombre in ['Regresión Logística', 'SVM'] else X_test
    cv_auc = cross_val_score(modelo, X, y, cv=cv, scoring='roc_auc').mean()

    resultados.append({
        'Algoritmo': nombre, 'AUC-ROC': auc, 'AUC-CV (5-fold)': cv_auc,
        'Accuracy': acc, 'Precision': prec, 'Recall': rec, 'F1': f1
    })
    print(f"   AUC-ROC: {auc:.4f} | AUC-CV: {cv_auc:.4f} | Recall: {rec:.4f} | F1: {f1:.4f}")

# ── 7. Tabla comparativa ──
print("\n" + "=" * 65)
print("TABLA COMPARATIVA DE ALGORITMOS")
print("=" * 65)
res_df = pd.DataFrame(resultados).sort_values('AUC-ROC', ascending=False)
print(res_df.to_string(index=False, float_format=lambda x: f"{x:.4f}"))

# ── 8. Seleccionar ganador ──
ganador_nombre = res_df.iloc[0]['Algoritmo']
ganador_auc    = res_df.iloc[0]['AUC-ROC']

print(f"\n🏆 ALGORITMO GANADOR: {ganador_nombre}")
print(f"   AUC-ROC: {ganador_auc:.4f}")

# Reentrenar ganador con todos los datos de train
print(f"\n🔁 Reentrenando {ganador_nombre} con todos los datos...")
_, (modelo_final, X_tr_f, _) = [(k, v) for k, v in algoritmos.items() if k == ganador_nombre][0]
modelo_final.fit(X_tr_f, y_train_bal)
y_prob_final = modelo_final.predict_proba(X_test_sc if ganador_nombre in ['Regresión Logística','SVM'] else X_test)[:,1]
y_pred_final = modelo_final.predict(X_test_sc if ganador_nombre in ['Regresión Logística','SVM'] else X_test)
auc_final = roc_auc_score(y_test, y_prob_final)

print(f"\n📈 Resultados finales del modelo elegido ({ganador_nombre}):")
print(f"   AUC-ROC: {auc_final:.4f}")
print(f"\n{classification_report(y_test, y_pred_final, target_names=['Al día','Incumplimiento'])}")

# ── 9. Verificar ERS ──
print(f"✅ Verificación ERS:")
print(f"   AUC-ROC ≥ 0.75: {'✅ CUMPLE' if auc_final >= 0.75 else '❌ NO CUMPLE'} ({auc_final:.4f})")
recall_mora = recall_score(y_test, y_pred_final, zero_division=0)
print(f"   Recall  ≥ 0.65: {'✅ CUMPLE' if recall_mora >= 0.65 else '❌ NO CUMPLE'} ({recall_mora:.4f})")

# ── 10. Guardar ──
os.makedirs(os.path.join(os.path.dirname(__file__), 'modelos'), exist_ok=True)
ruta_modelo = os.path.join(os.path.dirname(__file__), 'modelos', 'modelo_mora.pkl')

joblib.dump({
    'model':            modelo_final,
    'features':         features,
    'algoritmo':        ganador_nombre,
    'auc_roc':          auc_final,
    'scaler':           scaler if ganador_nombre in ['Regresión Logística','SVM'] else None,
    'tabla_comparativa': res_df.to_dict('records'),
    'feature_engineering': True,
}, ruta_modelo)

print(f"\n💾 Modelo guardado: {ruta_modelo}")
print(f"\n{'='*65}")
print("¡Entrenamiento y comparación completados!")
print(f"{'='*65}")