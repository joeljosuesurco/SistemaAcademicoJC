package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.jsq.sistemaacademicojct.adapter.HijoAdapter;
import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.HijoResponse;

import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class SeleccionarHijoParaSeguimientoActivity extends AppCompatActivity {

    RecyclerView recycler;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_seleccionar_hijo_notas); // ✅ reutiliza el layout

        recycler = findViewById(R.id.recyclerHijosNotas);
        recycler.setLayoutManager(new LinearLayoutManager(this));

        cargarHijos();
    }

    private void cargarHijos() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getHijosPadre().enqueue(new Callback<HijoResponse>() {
            @Override
            public void onResponse(Call<HijoResponse> call, Response<HijoResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<HijoResponse.Hijo> hijos = response.body().data;
                    recycler.setAdapter(new HijoAdapter(hijos, SeleccionarHijoParaSeguimientoActivity.this, true));
                } else {
                    Toast.makeText(SeleccionarHijoParaSeguimientoActivity.this, "No se pudieron cargar los hijos", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<HijoResponse> call, Throwable t) {
                Toast.makeText(SeleccionarHijoParaSeguimientoActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
