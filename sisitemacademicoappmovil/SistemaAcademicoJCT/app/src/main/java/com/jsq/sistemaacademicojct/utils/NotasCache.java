package com.jsq.sistemaacademicojct.utils;

import com.jsq.sistemaacademicojct.model.NotasEstudianteResponse;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class NotasCache {
    private static final Map<Integer, List<NotasEstudianteResponse.Nota>> cacheNotas = new HashMap<>();

    public static void guardarNotas(Map<Integer, List<NotasEstudianteResponse.Nota>> data) {
        cacheNotas.clear();
        cacheNotas.putAll(data);
    }

    public static Map<Integer, List<NotasEstudianteResponse.Nota>> obtenerNotas() {
        return cacheNotas;
    }

    public static void limpiar() {
        cacheNotas.clear();
    }
}
