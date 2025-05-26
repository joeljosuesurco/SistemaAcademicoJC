package com.jsq.sistemaacademicojct.api;

import com.jsq.sistemaacademicojct.model.HijoResponse;
import com.jsq.sistemaacademicojct.model.HorarioEstudianteResponse;
import com.jsq.sistemaacademicojct.model.HorarioEstudianteWrapper;
import com.jsq.sistemaacademicojct.model.LoginRequest;
import com.jsq.sistemaacademicojct.model.LoginResponse;
import com.jsq.sistemaacademicojct.model.NotaHijoResponse;
import com.jsq.sistemaacademicojct.model.NotificacionUsuarioResponse;
import com.jsq.sistemaacademicojct.model.PerfilResponse;
import com.jsq.sistemaacademicojct.model.EstudianteDatosResponse;
import com.jsq.sistemaacademicojct.model.NotasEstudianteResponse;
import com.jsq.sistemaacademicojct.model.SeguimientoEstudianteResponse;

import java.util.List;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Path;
import retrofit2.http.Query;

public interface UsuarioService {

    @POST("login")
    Call<LoginResponse> login(@Body LoginRequest request);

    @GET("perfil")
    Call<PerfilResponse> getPerfil();

    // ✅ Nuevo método para el rol estudiante
    @GET("estudiante-auth/datos")
    Call<EstudianteDatosResponse> getDatosEstudiante();
    @GET("estudiante-auth/notas")
    Call<NotasEstudianteResponse> getNotasEstudiante(
            @Query("periodo") String periodo,
            @Query("numero_periodo") int numeroPeriodo
    );

    @GET("estudiante-auth/seguimientos")
    Call<SeguimientoEstudianteResponse> getSeguimientosEstudiante();


    @GET("estudiante-auth/horario")
    Call<HorarioEstudianteWrapper> getHorarioEstudiante();

    @GET("mis-notificaciones")
    Call<NotificacionUsuarioResponse> getMisNotificaciones();

    @GET("notificacion-usuario/{userId}/no-leidas")
    Call<NotificacionUsuarioResponse> getNotificacionesNoLeidas(@Path("userId") int userId);

    @PUT("notificaciones/{id}/marcar-leido")
    Call<Void> marcarNotificacionLeida(@retrofit2.http.Path("id") int id);

    @PUT("notificaciones/{id}/marcar-leido")
    Call<NotificacionUsuarioResponse> marcarNotificacionComoLeida(@Path("id") int notificacionId);

    @GET("padre-auth/hijos")
    Call<HijoResponse> getHijosPadre(); // 👈 ESTE MÉTODO AGREGA

    @GET("padre-auth/notas/{id}")
    Call<NotaHijoResponse> getNotasHijo(
            @Path("id") int estudianteId,
            @Query("periodo") String periodo,
            @Query("numero_periodo") int numeroPeriodo
    );

    @GET("padre-auth/seguimientos/{idEstudiante}")
    Call<SeguimientoEstudianteResponse> getSeguimientosHijo(@Path("idEstudiante") int idEstudiante);

    @GET("padre-auth/horario/{idEstudiante}")
    Call<HorarioEstudianteWrapper> getHorarioHijo(@Path("idEstudiante") int idEstudiante);



}