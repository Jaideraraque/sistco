import joblib

m = joblib.load('modelos/modelo_ingresos.pkl')

nuevo = {
    'modelo':            None,
    'modelo_lr':         m['modelo_lr'],
    'modelo_arima':      m.get('modelo_arima', None),
    'algoritmo_ganador': m['algoritmo_ganador'],
    'tabla_comparativa': m['tabla_comparativa'],
    'serie':             m['serie'],
    'marzo_excluido':    m.get('marzo_excluido', False),
    'justificacion':     m.get('justificacion', ''),
    'feat_cols':         m.get('feat_cols', []),
    'n_meses_serie':     m.get('n_meses_serie', 0),
}

joblib.dump(nuevo, 'modelos/modelo_ingresos.pkl')
print('Listo - modelo re-exportado sin Prophet')