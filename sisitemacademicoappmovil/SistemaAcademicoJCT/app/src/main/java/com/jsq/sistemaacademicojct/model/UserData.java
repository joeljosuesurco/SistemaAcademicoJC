package com.jsq.sistemaacademicojct.model;

public class UserData {
    private int id_user;
    private String name_user;
    private String state_user;
    private int persona_rol_id_persona_rol;
    private PersonaRol persona_rol;

    public int getId_user() {
        return id_user;
    }

    public String getName_user() {
        return name_user;
    }

    public String getState_user() {
        return state_user;
    }

    public int getPersonaRolId() {
        return persona_rol_id_persona_rol;
    }

    public PersonaRol getPersonaRol() {
        return persona_rol;
    }
}
