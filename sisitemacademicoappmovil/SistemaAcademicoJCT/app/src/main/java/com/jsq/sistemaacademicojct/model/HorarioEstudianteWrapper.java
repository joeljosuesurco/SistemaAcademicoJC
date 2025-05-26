package com.jsq.sistemaacademicojct.model;

import java.util.List;

public class HorarioEstudianteWrapper {
    public boolean success;
    public List<HorarioEstudianteResponse> data;

    public List<HorarioEstudianteResponse> getData() {
        return data;
    }
}
