package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.model.HijoResponse;

public class PerfilHijoActivity extends AppCompatActivity {

    TextView txtNombreCompleto, txtNacimiento, txtSexo, txtNacionalidad, txtDireccion,
            txtCelular, txtCI, txtCertNac, txtLibreta, txtCurso;

    Button btnVerHorarioHijo;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_perfil_hijo);

        txtNombreCompleto = findViewById(R.id.txtNombreCompleto);
        txtNacimiento = findViewById(R.id.txtNacimiento);
        txtSexo = findViewById(R.id.txtSexo);
        txtNacionalidad = findViewById(R.id.txtNacionalidad);
        txtDireccion = findViewById(R.id.txtDireccion);
        txtCelular = findViewById(R.id.txtCelular);
        txtCI = findViewById(R.id.txtCI);
        txtCertNac = findViewById(R.id.txtCertNac);
        txtLibreta = findViewById(R.id.txtLibreta);
        txtCurso = findViewById(R.id.txtCurso);
        btnVerHorarioHijo = findViewById(R.id.btnVerHorarioHijo);

        HijoResponse.Hijo hijo = (HijoResponse.Hijo) getIntent().getSerializableExtra("hijo");

        if (hijo != null) {
            txtNombreCompleto.setText("Nombre: " + hijo.nombreCompleto);
            txtNacimiento.setText("Nacimiento: " + hijo.fechaNacimiento);
            txtSexo.setText("Sexo: " + hijo.sexo);
            txtNacionalidad.setText("Nacionalidad: " + hijo.nacionalidad);
            txtDireccion.setText("Dirección: " + hijo.direccion);
            txtCelular.setText("Celular: " + hijo.celular);
            txtCI.setText("CI: " + hijo.ci);
            txtCertNac.setText("Cert. Nac.: " + hijo.certNac);
            txtLibreta.setText("Libreta Anterior: " + hijo.libreta);
            txtCurso.setText("Curso: " + hijo.curso.grado + " " + hijo.curso.paralelo + " - " + hijo.curso.nivel);

            btnVerHorarioHijo.setOnClickListener(v -> {
                Intent intent = new Intent(PerfilHijoActivity.this, HorarioHijoActivity.class);
                intent.putExtra("id_estudiante", hijo.idEstudiante);
                startActivity(intent);
            });
        }
    }
}
