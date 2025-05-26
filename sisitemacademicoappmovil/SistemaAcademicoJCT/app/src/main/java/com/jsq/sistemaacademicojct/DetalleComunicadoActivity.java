package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

public class DetalleComunicadoActivity extends AppCompatActivity {

    TextView txtTitulo, txtMensaje, txtFecha;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_detalle_comunicado);
        getSupportActionBar().hide();

        txtTitulo = findViewById(R.id.txtTitulo);
        txtMensaje = findViewById(R.id.txtMensaje);
        txtFecha = findViewById(R.id.txtFecha);

        // Obtener datos enviados
        String titulo = getIntent().getStringExtra("titulo");
        String mensaje = getIntent().getStringExtra("mensaje");
        String fecha = getIntent().getStringExtra("fecha");

        txtTitulo.setText(titulo);
        txtMensaje.setText(mensaje);
        txtFecha.setText("📅 " + fecha);
    }

}
