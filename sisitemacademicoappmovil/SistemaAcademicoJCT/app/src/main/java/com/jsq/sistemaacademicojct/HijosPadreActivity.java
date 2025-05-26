package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.jsq.sistemaacademicojct.adapter.HijoAdapter;
import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.HijoResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class HijosPadreActivity extends AppCompatActivity {

    RecyclerView recyclerHijos;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hijos_padre);

        recyclerHijos = findViewById(R.id.recyclerHijos);
        recyclerHijos.setLayoutManager(new LinearLayoutManager(this));

        cargarHijos();
    }

    private void cargarHijos() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getHijosPadre().enqueue(new Callback<HijoResponse>() {
            @Override
            public void onResponse(Call<HijoResponse> call, Response<HijoResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    recyclerHijos.setAdapter(new HijoAdapter(response.body().data, HijosPadreActivity.this));
                } else {
                    Toast.makeText(HijosPadreActivity.this, "No se pudo cargar la lista", Toast.LENGTH_SHORT).show();
                }
            }


            @Override
            public void onFailure(Call<HijoResponse> call, Throwable t) {
                Toast.makeText(HijosPadreActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
