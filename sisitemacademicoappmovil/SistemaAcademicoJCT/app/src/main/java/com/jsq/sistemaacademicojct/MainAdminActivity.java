package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class MainAdminActivity extends AppCompatActivity {
    /*
    TextView txtBienvenida;
    Button btnGestionEstudiantes, btnGestionProfesores, btnGestionCursos, btnGestionComunicados;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main_admin);

        txtBienvenida = findViewById(R.id.txtBienvenidaAdmin);
        btnGestionEstudiantes = findViewById(R.id.btnEstudiantes);
        btnGestionProfesores = findViewById(R.id.btnProfesores);
        btnGestionCursos = findViewById(R.id.btnCursos);
        btnGestionComunicados = findViewById(R.id.btnComunicadosAdmin);

        SharedPreferences prefs = getSharedPreferences("datos_usuario", MODE_PRIVATE);
        String nombre = prefs.getString("usuario", "Administrador");

        txtBienvenida.setText("Bienvenido, " + nombre);

        btnGestionEstudiantes.setOnClickListener(v ->
                startActivity(new Intent(MainAdminActivity.this, ListaEstudiantesActivity.class))
        );

        btnGestionProfesores.setOnClickListener(v ->
                startActivity(new Intent(MainAdminActivity.this, ListaProfesoresActivity.class))
        );

        btnGestionCursos.setOnClickListener(v ->
                startActivity(new Intent(MainAdminActivity.this, ListaCursosActivity.class))
        );

        btnGestionComunicados.setOnClickListener(v ->
                startActivity(new Intent(MainAdminActivity.this, ListaComunicadosActivity.class))
        );
    }
    */
}
