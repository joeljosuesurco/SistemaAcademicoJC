package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.PerfilResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PerfilPadreActivity extends AppCompatActivity {

    TextView txtNombre, txtNacimiento, txtSexo,
            txtDireccion, txtNacionalidad, txtCelular;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_perfil_padre);
        getSupportActionBar().hide(); // Oculta la ActionBar

        txtNombre = findViewById(R.id.txtNombrePerfil);
        txtNacimiento = findViewById(R.id.txtNacimiento);
        txtSexo = findViewById(R.id.txtSexo);
        txtDireccion = findViewById(R.id.txtDireccion);
        txtNacionalidad = findViewById(R.id.txtNacionalidad);
        txtCelular = findViewById(R.id.txtCelular);

        cargarDatos();
    }

    private void cargarDatos() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);

        service.getPerfil().enqueue(new Callback<PerfilResponse>() {
            @Override
            public void onResponse(Call<PerfilResponse> call, Response<PerfilResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    PerfilResponse.Persona persona = response.body().getData().getPersonaRol().getPersona();

                    String nombreCompleto = safe(persona.getNombres_persona()) + " " +
                            safe(persona.getApellidos_pat()) + " " +
                            safe(persona.getApellidos_mat());

                    txtNombre.setText(nombreCompleto);
                    txtNacimiento.setText(safe(persona.getFecha_nacimiento()));
                    txtSexo.setText(safe(persona.getSexo_persona()));
                    txtDireccion.setText(safe(persona.getDireccion_persona()));
                    txtNacionalidad.setText(safe(persona.getNacionalidad_persona()));
                    txtCelular.setText(safe(persona.getCelular_persona()));

                } else {
                    Toast.makeText(PerfilPadreActivity.this, "No se pudo cargar el perfil", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<PerfilResponse> call, Throwable t) {
                Toast.makeText(PerfilPadreActivity.this, "Error de conexión: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String safe(String value) {
        return (value != null) ? value : "-";
    }
}
