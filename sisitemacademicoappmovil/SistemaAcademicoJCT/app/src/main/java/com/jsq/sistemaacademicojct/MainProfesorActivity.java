package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class MainProfesorActivity extends AppCompatActivity {
    /*
    TextView txtBienvenida;
    Button btnCursos, btnNotas, btnSeguimiento;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main_profesor);

        txtBienvenida = findViewById(R.id.txtBienvenidaProfesor);
        btnCursos = findViewById(R.id.btnCursos);
        btnNotas = findViewById(R.id.btnNotas);
        btnSeguimiento = findViewById(R.id.btnSeguimiento);

        // Recuperar nombre del usuario desde SharedPreferences
        SharedPreferences prefs = getSharedPreferences("datos_usuario", MODE_PRIVATE);
        String usuario = prefs.getString("usuario", "Profesor");

        txtBienvenida.setText("Bienvenido, " + usuario);

        btnCursos.setOnClickListener(v -> {
            startActivity(new Intent(MainProfesorActivity.this, CursosProfesorActivity.class));
        });

        btnNotas.setOnClickListener(v -> {
            startActivity(new Intent(MainProfesorActivity.this, NotasProfesorView.class));
        });

        btnSeguimiento.setOnClickListener(v -> {
            startActivity(new Intent(MainProfesorActivity.this, SeguimientoProfesorView.class));
        });
    }*/
}
