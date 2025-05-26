package com.jsq.sistemaacademicojct.model;

public class EstudianteDatosResponse {
    private boolean success;
    private Datos data;

    public boolean isSuccess() {
        return success;
    }

    public Datos getData() {
        return data;
    }

    public static class Datos {
        private int id_estudiante;
        private String rude;
        private String nombre_completo;
        private String apellidos_pat;
        private String apellidos_mat;
        private String nombres;
        private String fecha_nacimiento;
        private String sexo;
        private String nacionalidad;
        private String direccion;
        private String celular;
        private String foto;
        private String ci;
        private String cert_nac;
        private String libreta;
        private Curso curso;

        public int getId_estudiante() {
            return id_estudiante;
        }

        public String getRude() {
            return rude;
        }

        public String getNombre_completo() {
            return nombre_completo;
        }

        public String getApellidos_pat() {
            return apellidos_pat;
        }

        public String getApellidos_mat() {
            return apellidos_mat;
        }

        public String getNombres() {
            return nombres;
        }

        public String getFecha_nacimiento() {
            return fecha_nacimiento;
        }

        public String getSexo() {
            return sexo;
        }

        public String getNacionalidad() {
            return nacionalidad;
        }

        public String getDireccion() {
            return direccion;
        }

        public String getCelular() {
            return celular;
        }

        public String getFoto() {
            return foto;
        }

        public String getCi() {
            return ci;
        }

        public String getCert_nac() {
            return cert_nac;
        }

        public String getLibreta() {
            return libreta;
        }

        public Curso getCurso() {
            return curso;
        }

        public String getNombreCompleto() {
            return nombre_completo != null ? nombre_completo : (nombres + " " + apellidos_pat + " " + apellidos_mat);
        }

        public String getCursoNombre() {
            if (curso != null) {
                return curso.grado_curso + " " + curso.paralelo_curso;
            } else {
                return "Sin curso asignado";
            }
        }

        public static class Curso {
            private String grado_curso;
            private String paralelo_curso;
            private String nivel;
            private int id_curso;

            public String getGrado_curso() {
                return grado_curso;
            }

            public String getParalelo_curso() {
                return paralelo_curso;
            }

            public String getNivel() {
                return nivel;
            }

            public int getId_curso() {
                return id_curso;
            }
        }
    }
}
