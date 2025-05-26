package com.jsq.sistemaacademicojct.adapter;

import android.content.Context;
import android.content.Intent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.jsq.sistemaacademicojct.PerfilHijoActivity;
import com.jsq.sistemaacademicojct.R;
import com.jsq.sistemaacademicojct.SeguimientoHijoActivity;
import com.jsq.sistemaacademicojct.model.HijoResponse;

import java.util.List;

public class HijoAdapter extends RecyclerView.Adapter<HijoAdapter.HijoViewHolder> {

    private final List<HijoResponse.Hijo> hijos;
    private final Context context;
    private final boolean modoSeguimiento;

    // Constructor estándar (perfil)
    public HijoAdapter(List<HijoResponse.Hijo> hijos, Context context) {
        this(hijos, context, false);
    }

    // Constructor con opción de modo seguimiento
    public HijoAdapter(List<HijoResponse.Hijo> hijos, Context context, boolean modoSeguimiento) {
        this.hijos = hijos;
        this.context = context;
        this.modoSeguimiento = modoSeguimiento;
    }

    @NonNull
    @Override
    public HijoViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View vista = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_hijo, parent, false);
        return new HijoViewHolder(vista);
    }

    @Override
    public void onBindViewHolder(@NonNull HijoViewHolder holder, int position) {
        HijoResponse.Hijo hijo = hijos.get(position);
        holder.txtNombre.setText(hijo.nombreCompleto);
        holder.txtCurso.setText(hijo.curso.grado + " " + hijo.curso.paralelo + " - " + hijo.curso.nivel);

        holder.itemView.setOnClickListener(v -> {
            Intent intent;
            if (modoSeguimiento) {
                intent = new Intent(context, SeguimientoHijoActivity.class);
                intent.putExtra("id_estudiante", hijo.idEstudiante);
            } else {
                intent = new Intent(context, PerfilHijoActivity.class);
                intent.putExtra("hijo", hijo); // Hijo debe implementar Serializable
            }
            context.startActivity(intent);
        });
    }

    @Override
    public int getItemCount() {
        return hijos.size();
    }

    static class HijoViewHolder extends RecyclerView.ViewHolder {
        TextView txtNombre, txtCurso;

        public HijoViewHolder(@NonNull View itemView) {
            super(itemView);
            txtNombre = itemView.findViewById(R.id.txtNombreHijo);
            txtCurso = itemView.findViewById(R.id.txtCursoHijo);
        }
    }
}
