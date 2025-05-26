package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import com.jsq.sistemaacademicojct.api.RetrofitClient;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

public class MainActivity extends AppCompatActivity {

    Button btnPerfil, btnLogout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        getSupportActionBar().hide();
        setContentView(R.layout.activity_main);

        btnPerfil = findViewById(R.id.buttonPerfil);
        btnLogout = findViewById(R.id.buttonLogout);

        btnPerfil.setOnClickListener(v -> {
            // Ir al perfil del estudiante manualmente
            Intent intent = new Intent(MainActivity.this, PerfilEstudianteActivity.class);
            startActivity(intent);
        });


        btnLogout.setOnClickListener(v -> {
            // Limpiar token (si se desea persistencia futura, también desde SharedPreferences)
            RetrofitClient.setToken(null);

            Intent intent = new Intent(MainActivity.this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        });
    }
}
