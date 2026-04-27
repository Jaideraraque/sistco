import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error
import joblib
import os
import warnings
warnings.filterwarnings('ignore')

print("=" * 65)
print("SISTCO-ML — Proyección de Ingresos")
print("=" * 65)

# ── 1. Cargar dataset ──
ruta_excel = os.path.join(os.path.dirname(__file__), '..', 'database', 'seeders', 'data', 'Datos_SISTCO.xlsx')
df_raw = pd.read_excel(ruta_excel, header=None)
print(f"\n✅ Excel cargado: {df_raw.shape[0]} filas x {df_raw.shape[1]} columnas")

# ── 2. Identificar columna de mensualidad ──
mensualidad_col = None
for col in df_raw.columns:
    val = str(df_raw[col].iloc[0]).lower().strip()
    if 'mensualidad' in val or 'mensual' in val:
        mensualidad_col = col
        break
print(f"   Columna mensualidad detectada: columna {mensualidad_col}")

# ── 3. Construir serie temporal ──
print("\n📅 Construyendo serie temporal de ingresos...")

meses_cols = []
ingresos   = []

for col in df_raw.columns:
    header_val = str(df_raw[col].iloc[0]).strip()
    fecha = None

    # Intentar múltiples formatos de fecha
    for fmt in ['%Y-%m-%d', '%d/%m/%Y', '%m/%d/%Y', '%Y-%m',
                '%b-%y', '%B-%Y', '%b %Y', '%B %Y',
                '%d-%m-%Y', '%Y/%m/%d']:
        try:
            fecha = pd.to_datetime(header_val, format=fmt)
            break
        except:
            pass

    # Intento genérico
    if fecha is None:
        try:
            fecha = pd.to_datetime(header_val)
        except:
            pass

    if fecha is None:
        continue
    if fecha.year < 2019 or fecha.year > 2026:
        continue

    # Contar pagos OK
    valores = df_raw[col].iloc[1:].astype(str).str.upper().str.strip()
    mask_ok = valores.isin(['OK', '1', 'TRUE', 'SI', 'S', 'PAGO', 'P'])

    if mensualidad_col is not None:
        mensualidades = pd.to_numeric(
            df_raw[mensualidad_col].iloc[1:], errors='coerce'
        ).fillna(0).values
        ingreso_mes = float(mensualidades[mask_ok.values].sum())
    else:
        ingreso_mes = float(mask_ok.sum() * 100000)

    if ingreso_mes > 0:
        meses_cols.append(fecha)
        ingresos.append(ingreso_mes)

print(f"   Meses detectados: {len(meses_cols)}")

# ── 4. Si no detectó nada, reconstruir desde CSV ──
if len(meses_cols) < 5:
    print("\n⚠️  Pocas fechas detectadas en Excel.")
    print("   Reconstruyendo serie desde dataset_limpio_SISTCO.csv...")

    ruta_csv = os.path.join(os.path.dirname(__file__), '..', 'database', 'seeders', 'data', 'dataset_limpio_SISTCO.csv')
    df_csv   = pd.read_csv(ruta_csv)
    fecha_ref = pd.Timestamp('2026-02-01')
    ingresos_por_mes = {}

    for _, row in df_csv.iterrows():
        meses_activos = int(row.get('n_meses_activos', 0))
        mensualidad   = float(row.get('Mensualidad', 0))
        n_moras       = int(row.get('n_moras_historicas', 0))
        pagos_ok      = meses_activos - n_moras
        prob_ok       = pagos_ok / max(meses_activos, 1)

        for m in range(meses_activos):
            fecha_mes = fecha_ref - pd.DateOffset(months=m)
            key = fecha_mes.strftime('%Y-%m')
            if key not in ingresos_por_mes:
                ingresos_por_mes[key] = 0
            ingresos_por_mes[key] += mensualidad * prob_ok

    meses_cols = []
    ingresos   = []
    for key_mes, ingreso in sorted(ingresos_por_mes.items()):
        try:
            fecha = pd.to_datetime(key_mes + '-01')
            if 2019 <= fecha.year <= 2026 and ingreso > 1000:
                meses_cols.append(fecha)
                ingresos.append(ingreso)
        except:
            continue

    print(f"   Meses reconstruidos: {len(meses_cols)}")

# ── 5. Construir DataFrame ──
serie = pd.DataFrame({'ds': pd.to_datetime(meses_cols), 'y': ingresos})
serie = serie.sort_values('ds').drop_duplicates('ds').reset_index(drop=True)

# ── 6. Quitar Marzo 2026 ──
antes = len(serie)
serie = serie[~((serie['ds'].dt.year == 2026) & (serie['ds'].dt.month == 3))].reset_index(drop=True)
if antes > len(serie):
    print(f"\n⚠️  Marzo 2026 excluido — datos incompletos al momento de extracción del dataset")

print(f"\n   Serie final: {len(serie)} meses")
print(f"   Período: {serie['ds'].min().strftime('%b %Y')} → {serie['ds'].max().strftime('%b %Y')}")
print(f"   Ingreso mínimo:   ${serie['y'].min():>12,.0f}")
print(f"   Ingreso máximo:   ${serie['y'].max():>12,.0f}")
print(f"   Ingreso promedio: ${serie['y'].mean():>12,.0f}")

if len(serie) < 10:
    print("\n❌ Serie temporal insuficiente. Revisa el Excel.")
    exit(1)

# ── 7. Feature Engineering ──
serie['mes_num']      = np.arange(len(serie))
serie['mes_anio']     = serie['ds'].dt.month
serie['trimestre']    = serie['ds'].dt.quarter
serie['sin_estacion'] = np.sin(2 * np.pi * serie['ds'].dt.month / 12)
serie['cos_estacion'] = np.cos(2 * np.pi * serie['ds'].dt.month / 12)
serie['lag_1']        = serie['y'].shift(1)
serie['lag_3']        = serie['y'].shift(3)
serie['media_mov_3']  = serie['y'].rolling(3).mean()
serie_fe = serie.dropna().reset_index(drop=True)

print(f"\n🔧 Feature Engineering aplicado — serie para modelos: {len(serie_fe)} meses")

# ── 8. Split ──
n_test  = min(6, int(len(serie_fe) * 0.15))
n_train = len(serie_fe) - n_test
serie_train = serie_fe.iloc[:n_train]
serie_test  = serie_fe.iloc[n_train:]
feat_cols   = ['mes_num','sin_estacion','cos_estacion','lag_1','lag_3','media_mov_3']
X_tr = serie_train[feat_cols].values
y_tr = serie_train['y'].values
X_te = serie_test[feat_cols].values
y_te = serie_test['y'].values
print(f"   Train: {n_train} meses | Test: {n_test} meses")

resultados     = []
modelo_prophet = None
modelo_arima   = None

# ── Modelo A: Prophet ──
try:
    from prophet import Prophet
    print("\n🔄 Entrenando Prophet...")
    n_prophet = n_train + (len(serie) - len(serie_fe))
    df_p = serie[['ds','y']].iloc[:n_prophet].copy()
    m = Prophet(yearly_seasonality=True, seasonality_mode='additive',
                changepoint_prior_scale=0.10, interval_width=0.95)
    m.fit(df_p)
    futuro   = m.make_future_dataframe(periods=n_test, freq='MS')
    forecast = m.predict(futuro)
    pred_p   = forecast.tail(n_test)['yhat'].values
    mae_p    = mean_absolute_error(y_te, pred_p)
    mape_p   = np.mean(np.abs((y_te - pred_p) / (np.abs(y_te) + 1))) * 100
    rmse_p   = np.sqrt(mean_squared_error(y_te, pred_p))
    resultados.append({'Algoritmo':'Prophet','MAE':mae_p,'MAPE%':mape_p,'RMSE':rmse_p})
    print(f"   MAPE: {mape_p:.2f}% | MAE: ${mae_p:,.0f} | RMSE: ${rmse_p:,.0f}")
    modelo_prophet   = m
    forecast_prophet = forecast
except Exception as e:
    print(f"   ⚠️  Prophet: {e}")

# ── Modelo B: Regresión Lineal Temporal ──
print("\n🔄 Entrenando Regresión Lineal Temporal...")
lr = LinearRegression()
lr.fit(X_tr, y_tr)
pred_lr = lr.predict(X_te)
mae_lr  = mean_absolute_error(y_te, pred_lr)
mape_lr = np.mean(np.abs((y_te - pred_lr) / (np.abs(y_te) + 1))) * 100
rmse_lr = np.sqrt(mean_squared_error(y_te, pred_lr))
resultados.append({'Algoritmo':'Regresión Lineal Temporal','MAE':mae_lr,'MAPE%':mape_lr,'RMSE':rmse_lr})
print(f"   MAPE: {mape_lr:.2f}% | MAE: ${mae_lr:,.0f} | RMSE: ${rmse_lr:,.0f}")

# ── Modelo C: ARIMA ──
try:
    from statsmodels.tsa.arima.model import ARIMA
    print("\n🔄 Entrenando ARIMA(2,1,2)...")
    n_arima   = n_train + (len(serie) - len(serie_fe))
    y_arima   = serie['y'].iloc[:n_arima].values
    arima_fit = ARIMA(y_arima, order=(2, 1, 2)).fit()
    pred_a    = arima_fit.forecast(steps=n_test)
    mae_a     = mean_absolute_error(y_te, pred_a)
    mape_a    = np.mean(np.abs((y_te - pred_a) / (np.abs(y_te) + 1))) * 100
    rmse_a    = np.sqrt(mean_squared_error(y_te, pred_a))
    resultados.append({'Algoritmo':'ARIMA(2,1,2)','MAE':mae_a,'MAPE%':mape_a,'RMSE':rmse_a})
    print(f"   MAPE: {mape_a:.2f}% | MAE: ${mae_a:,.0f} | RMSE: ${rmse_a:,.0f}")
    modelo_arima = arima_fit
except Exception as e:
    print(f"   ⚠️  ARIMA: {e}")

# ── 9. Tabla comparativa ──
print("\n" + "=" * 65)
print("TABLA COMPARATIVA — PROYECCIÓN DE INGRESOS")
print("=" * 65)
res_df = pd.DataFrame(resultados).sort_values('MAPE%')
print(res_df.to_string(index=False, float_format=lambda x: f"{x:.2f}"))
ganador_nombre = res_df.iloc[0]['Algoritmo']
ganador_mape   = res_df.iloc[0]['MAPE%']
print(f"\n🏆 ALGORITMO GANADOR: {ganador_nombre} (MAPE: {ganador_mape:.2f}%)")
cumple = ganador_mape <= 12
print(f"   MAPE ≤ 12% ERS: {'✅ CUMPLE' if cumple else '⚠️  Por encima del umbral, pero es el mejor disponible'}")

# ── 10. Proyección 6 meses ──
print(f"\n📅 Proyección próximos 6 meses ({ganador_nombre}):")
print(f"   {'Mes':<12} {'Proyectado':>15} {'Mín IC95%':>15} {'Máx IC95%':>15}")
print(f"   {'-'*60}")

if modelo_prophet and ganador_nombre == 'Prophet':
    futuro6 = modelo_prophet.make_future_dataframe(periods=6, freq='MS')
    fc6     = modelo_prophet.predict(futuro6)
    proy6   = fc6.tail(6)
    for _, row in proy6.iterrows():
        print(f"   {str(row.ds)[:7]:<12} ${row.yhat:>14,.0f} ${row.yhat_lower:>14,.0f} ${row.yhat_upper:>14,.0f}")
else:
    ultima_fecha = serie_fe['ds'].max()
    ultimo_idx   = int(serie_fe['mes_num'].max())
    hist_y       = list(serie_fe['y'].values)
    for i in range(1, 7):
        fecha_fut = ultima_fecha + pd.DateOffset(months=i)
        mes_n     = ultimo_idx + i
        sin_e     = np.sin(2 * np.pi * fecha_fut.month / 12)
        cos_e     = np.cos(2 * np.pi * fecha_fut.month / 12)
        lag1      = hist_y[-1]
        lag3      = hist_y[-3] if len(hist_y) >= 3 else hist_y[-1]
        mm3       = np.mean(hist_y[-3:])
        pred      = lr.predict([[mes_n, sin_e, cos_e, lag1, lag3, mm3]])[0]
        hist_y.append(pred)
        print(f"   {str(fecha_fut)[:7]:<12} ${pred:>14,.0f} {'N/A':>15} {'N/A':>15}")

# ── 11. Guardar ──
os.makedirs(os.path.join(os.path.dirname(__file__), 'modelos'), exist_ok=True)
ruta_modelo = os.path.join(os.path.dirname(__file__), 'modelos', 'modelo_ingresos.pkl')

joblib.dump({
    'modelo':            modelo_prophet,
    'modelo_lr':         lr,
    'modelo_arima':      modelo_arima,
    'algoritmo_ganador': ganador_nombre,
    'tabla_comparativa': res_df.to_dict('records'),
    'serie':             serie[['ds','y']].to_dict('records'),
    'marzo_excluido':    True,
    'justificacion':     'Marzo 2026 excluido — datos incompletos al momento de extracción del dataset',
    'feat_cols':         feat_cols,
    'n_meses_serie':     len(serie),
}, ruta_modelo)

print(f"\n💾 Modelo guardado: {ruta_modelo}")
print(f"\n{'='*65}")
print("¡Proyección completada!")
print(f"{'='*65}")
