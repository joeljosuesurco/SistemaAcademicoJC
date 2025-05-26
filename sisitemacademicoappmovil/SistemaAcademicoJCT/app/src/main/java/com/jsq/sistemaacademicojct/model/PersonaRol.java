package com.jsq.sistemaacademicojct.model;

public class PersonaRol {
    private int id_persona_rol;
    private int roles_id_rol;
    private int personas_id_persona;
    private Rol rol;
    private Persona persona;

    public int getId_persona_rol() {
        return id_persona_rol;
    }

    public Rol getRol() {
        return rol;
    }

    public Persona getPersona() {
        return persona;
    }
}
