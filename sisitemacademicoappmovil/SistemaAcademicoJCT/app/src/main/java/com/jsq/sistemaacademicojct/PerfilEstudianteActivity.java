package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.EstudianteDatosResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PerfilEstudianteActivity extends AppCompatActivity {

    TextView txtNombre, txtRude, txtCI, txtNacimiento, txtSexo,
            txtDireccion, txtNacionalidad, txtCelular,
            txtCurso, txtNivel, txtLibreta;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_perfil_estudiante);
        getSupportActionBar().hide(); // Oculta la ActionBar

        txtNombre = findViewById(R.id.txtNombrePerfil);
        txtRude = findViewById(R.id.txtRude);
        txtCI = findViewById(R.id.txtCI);
        txtNacimiento = findViewById(R.id.txtNacimiento);
        txtSexo = findViewById(R.id.txtSexo);
        txtDireccion = findViewById(R.id.txtDireccion);
        txtNacionalidad = findViewById(R.id.txtNacionalidad);
        txtCelular = findViewById(R.id.txtCelular);
        txtCurso = findViewById(R.id.txtCurso);
        txtNivel = findViewById(R.id.txtNivel);
        txtLibreta = findViewById(R.id.txtLibreta);

        cargarDatos();
    }

    private void cargarDatos() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);

        service.getDatosEstudiante().enqueue(new Callback<EstudianteDatosResponse>() {
            @Override
            public void onResponse(Call<EstudianteDatosResponse> call, Response<EstudianteDatosResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getData() != null) {
                    EstudianteDatosResponse.Datos e = response.body().getData();

                    txtNombre.setText(safe(e.getNombre_completo()));
                    txtRude.setText(safe(e.getRude()));
                    txtCI.setText(safe(e.getCi()));
                    txtNacimiento.setText(safe(e.getFecha_nacimiento()));
                    txtSexo.setText(safe(e.getSexo()));
                    txtDireccion.setText(safe(e.getDireccion()));
                    txtNacionalidad.setText(safe(e.getNacionalidad()));
                    txtCelular.setText(safe(e.getCelular()));
                    txtLibreta.setText(safe(e.getLibreta()));

                    if (e.getCurso() != null) {
                        txtCurso.setText(safe(e.getCurso().getGrado_curso() + " " + e.getCurso().getParalelo_curso()));
                        txtNivel.setText(safe(e.getCurso().getNivel()));
                    } else {
                        txtCurso.setText("Sin curso");
                        txtNivel.setText("-");
                    }

                } else {
                    Toast.makeText(PerfilEstudianteActivity.this, "No se pudo cargar el perfil del estudiante", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<EstudianteDatosResponse> call, Throwable t) {
                Toast.makeText(PerfilEstudianteActivity.this, "Error de conexión: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String safe(String value) {
        return (value != null) ? value : "-";
    }
}
