package com.jsq.sistemaacademicojct.model;

import java.util.List;

public class NotificacionUsuarioResponse {
    public boolean success;
    public String message;
    public List<Item> data;

    public static class Item {
        public int id;
        public int notificaciones_id_notificacion;
        public int users_id_user;
        public boolean leido;
        public String fecha_envio;
        public String fecha_lectura;
        public Notificacion notificacion;
    }

    public static class Notificacion {
        public int id_notificacion;
        public String titulo_notificacion;
        public String mensaje_notificacion;
        public String fecha_notificacion;
        public String estado_notificacion;
        public String tipo_notificacion;
        public int users_id_user;
    }
}
