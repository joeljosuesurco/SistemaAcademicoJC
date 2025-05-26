package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.anychart.AnyChart;
import com.anychart.AnyChartView;
import com.anychart.chart.common.dataentry.DataEntry;
import com.anychart.chart.common.dataentry.ValueDataEntry;
import com.anychart.charts.Radar;
import com.anychart.core.radar.series.Area;

import java.util.ArrayList;
import java.util.List;

public class DetalleSeguimientoActivity extends AppCompatActivity {

    private AnyChartView anyChartView;
    private TextView txtMateria, txtFecha, txtObservacion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_detalle_seguimiento);
        getSupportActionBar().hide();

        anyChartView = findViewById(R.id.any_chart_view);
        txtMateria = findViewById(R.id.txtMateria);
        txtFecha = findViewById(R.id.txtFecha);
        txtObservacion = findViewById(R.id.txtObservacion);

        Bundle extras = getIntent().getExtras();
        if (extras != null) {
            txtMateria.setText("📘 Materia: " + extras.getString("materia", ""));
            txtFecha.setText("🗓 Fecha: " + extras.getString("fecha", ""));
            txtObservacion.setText(extras.getString("observacion", ""));

            List<DataEntry> data = new ArrayList<>();
            data.add(new ValueDataEntry("Asistencia", mapAsistencia(extras.getString("asistencia"))));
            data.add(new ValueDataEntry("Participación", mapParticipacion(extras.getString("participacion"))));
            data.add(new ValueDataEntry("Disciplina", mapDesempeno(extras.getString("disciplina"))));
            data.add(new ValueDataEntry("Puntualidad", mapDesempeno(extras.getString("puntualidad"))));
            data.add(new ValueDataEntry("Respeto", mapSiNo(extras.getString("respeto"))));
            data.add(new ValueDataEntry("Tolerancia", mapSiNo(extras.getString("tolerancia"))));
            data.add(new ValueDataEntry("Ánimo", mapEstadoAnimo(extras.getString("estado_animo"))));

            Radar radar = AnyChart.radar();
            radar.title("Indicadores del Seguimiento");

            radar.yScale()
                    .minimum(0d)
                    .maximum(5d)
                    .ticks("{ interval: 1 }");

            Area area = radar.area(data);
            area.fill("#3949AB", 0.4d);
            area.stroke("2 #3949AB");
            area.markers(true);

            radar.tooltip().enabled(true).format("{%Value} / 5");

            anyChartView.setChart(radar);
        }
    }

    // ✅ Escala corregida según lógica personalizada

    private int mapAsistencia(String val) {
        if (val == null) return 0;
        return val.trim().equalsIgnoreCase("Sí") ? 5 : 0;
    }

    private int mapParticipacion(String val) {
        if (val == null) return 0;
        val = val.trim().toLowerCase();
        switch (val) {
            case "alta": return 5;
            case "media": return 3;
            case "baja": return 1;
            default: return 0;
        }
    }

    private int mapDesempeno(String val) {
        if (val == null) return 0;
        val = val.trim().toLowerCase();
        switch (val) {
            case "excelente": return 5;
            case "buena": return 4;
            case "regular": return 3;
            case "mala": return 2;
            default: return 0;
        }
    }

    private int mapSiNo(String val) {
        if (val == null) return 0;
        return val.trim().equalsIgnoreCase("Sí") ? 5 : 2;
    }

    private int mapEstadoAnimo(String val) {
        if (val == null) return 0;
        val = val.trim().toLowerCase();
        switch (val) {
            case "muy bien": return 5;
            case "bien":
            case "neutro": return 3;
            case "estresado": return 2;
            case "malo": return 1;
            default: return 0;
        }
    }
}
