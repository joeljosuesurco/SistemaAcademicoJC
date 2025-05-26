package com.jsq.sistemaacademicojct.model;

public class PerfilResponse {
    private boolean success;
    private String message;
    private PerfilData data;

    public boolean isSuccess() {
        return success;
    }

    public String getMessage() {
        return message;
    }

    public PerfilData getData() {
        return data;
    }

    public static class PerfilData {
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

    public static class PersonaRol {
        private int id_persona_rol;
        private int roles_id_rol;
        private int personas_id_persona;
        private Rol rol;
        private Persona persona;

        public int getId_persona_rol() {
            return id_persona_rol;
        }

        public int getRoles_id_rol() {
            return roles_id_rol;
        }

        public int getPersonas_id_persona() {
            return personas_id_persona;
        }

        public Rol getRol() {
            return rol;
        }

        public Persona getPersona() {
            return persona;
        }
    }

    public static class Rol {
        private int id_rol;
        private String nombre;

        public int getId_rol() {
            return id_rol;
        }

        public String getNombre() {
            return nombre;
        }
    }

    public static class Persona {
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
        private String ci;



        public String getNombres_persona() {
            return nombres_persona;
        }

        public String getApellidos_pat() {
            return apellidos_pat;
        }

        public String getApellidos_mat() {
            return apellidos_mat;
        }

        public String getSexo_persona() {
            return sexo_persona;
        }

        public String getFecha_nacimiento() {
            return fecha_nacimiento;
        }

        public String getDireccion_persona() {
            return direccion_persona;
        }

        public String getNacionalidad_persona() {
            return nacionalidad_persona;
        }

        public String getCelular_persona() {
            return celular_persona;
        }

        public String getFotografia_persona() {
            return fotografia_persona;
        }

        public String getCi() {
            return ci;
        }
    }
}
