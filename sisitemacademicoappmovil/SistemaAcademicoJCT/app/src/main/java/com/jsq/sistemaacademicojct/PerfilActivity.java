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

public class PerfilActivity extends AppCompatActivity {

    TextView txtNombre, txtNacimiento, txtDireccion, txtNacionalidad, txtCelular, txtSexo;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_perfil);

        getSupportActionBar().hide(); // Oculta la ActionBar superior

        txtNombre = findViewById(R.id.txtNombrePerfil);
        txtNacimiento = findViewById(R.id.txtNacimiento);
        txtDireccion = findViewById(R.id.txtDireccion);
        txtNacionalidad = findViewById(R.id.txtNacionalidad);
        txtCelular = findViewById(R.id.txtCelular);
        txtSexo = findViewById(R.id.txtSexo);

        cargarPerfil();
    }

    private void cargarPerfil() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);

        service.getDatosEstudiante().enqueue(new Callback<EstudianteDatosResponse>() {
            @Override
            public void onResponse(Call<EstudianteDatosResponse> call, Response<EstudianteDatosResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getData() != null) {
                    EstudianteDatosResponse.Datos datos = response.body().getData();

                    txtNombre.setText(datos.getNombreCompleto());

                    txtNacimiento.setText("Fecha de nacimiento: " + safe(datos.getFecha_nacimiento()));
                    txtDireccion.setText("Dirección: " + safe(datos.getDireccion()));
                    txtNacionalidad.setText("Nacionalidad: " + safe(datos.getNacionalidad()));
                    txtCelular.setText("Celular: " + safe(datos.getCelular()));
                    txtSexo.setText("Sexo: " + safe(datos.getSexo()));

                    if (datos.getCurso() != null) {
                        String curso = datos.getCurso().getGrado_curso() + " " +
                                datos.getCurso().getParalelo_curso() + " - " +
                                datos.getCurso().getNivel();

                        Toast.makeText(PerfilActivity.this, "Curso actual: " + curso, Toast.LENGTH_LONG).show();
                    }

                } else {
                    Toast.makeText(PerfilActivity.this, "No se pudieron cargar los datos del perfil", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<EstudianteDatosResponse> call, Throwable t) {
                Toast.makeText(PerfilActivity.this, "Error de conexión: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    // Utilidad para evitar mostrar "null"
    private String safe(String valor) {
        return (valor != null) ? valor : "-";
    }
}
