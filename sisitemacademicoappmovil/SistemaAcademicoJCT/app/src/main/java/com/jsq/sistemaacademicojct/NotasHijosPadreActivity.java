package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.jsq.sistemaacademicojct.adapter.BoletaNotasHijoAdapter;
import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.NotaHijoResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class NotasHijosPadreActivity extends AppCompatActivity {

    RecyclerView recyclerNotas;
    TextView txtTituloNotas;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notas_hijos_padre);

        recyclerNotas = findViewById(R.id.recyclerNotasHijo);
        txtTituloNotas = findViewById(R.id.txtTituloNotas);

        recyclerNotas.setLayoutManager(new LinearLayoutManager(this));

        int idEstudiante = getIntent().getIntExtra("id_estudiante", -1);
        if (idEstudiante != -1) {
            cargarNotas(idEstudiante);
        } else {
            Toast.makeText(this, "No se recibió ID de hijo", Toast.LENGTH_SHORT).show();
        }
    }

    private void cargarNotas(int idEstudiante) {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getNotasHijo(idEstudiante, "trimestre", 1).enqueue(new Callback<NotaHijoResponse>() {
            @Override
            public void onResponse(Call<NotaHijoResponse> call, Response<NotaHijoResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    recyclerNotas.setAdapter(new BoletaNotasHijoAdapter(response.body().data.notas));
                } else {
                    Toast.makeText(NotasHijosPadreActivity.this, "No hay notas disponibles", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<NotaHijoResponse> call, Throwable t) {
                Toast.makeText(NotasHijosPadreActivity.this, "Error al cargar notas", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
