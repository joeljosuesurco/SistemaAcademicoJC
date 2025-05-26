package com.jsq.sistemaacademicojct.model;

public class Persona {
    private int id_persona;
    private String nombres_persona;
    private String apellidos_pat;
    private String apellidos_mat;
    private String sexo_persona;
    private String fecha_nacimiento;
    private String direccion_persona;
    private String nacionalidad_persona;
    private String celular_persona;
    private String fotografia_persona;

    public String getNombres_persona() {
        return nombres_persona;
    }

    public String getApellidos_pat() {
        return apellidos_pat;
    }

    public String getApellidos_mat() {
        return apellidos_mat;
    }


    // Puedes agregar más getters si los necesitas
}
