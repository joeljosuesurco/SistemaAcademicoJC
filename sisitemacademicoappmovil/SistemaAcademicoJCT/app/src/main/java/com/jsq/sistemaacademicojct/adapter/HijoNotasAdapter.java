package com.jsq.sistemaacademicojct.adapter;

import android.content.Context;
import android.content.Intent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.jsq.sistemaacademicojct.NotasDetalleHijoActivity;
import com.jsq.sistemaacademicojct.R;
import com.jsq.sistemaacademicojct.model.HijoResponse;

import java.util.List;

public class HijoNotasAdapter extends RecyclerView.Adapter<HijoNotasAdapter.HijoViewHolder> {

    private final List<HijoResponse.Hijo> hijos;
    private final Context context;

    public HijoNotasAdapter(List<HijoResponse.Hijo> hijos, Context context) {
        this.hijos = hijos;
        this.context = context;
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
            Intent intent = new Intent(context, NotasDetalleHijoActivity.class);
            intent.putExtra("id_estudiante", hijo.idEstudiante);
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
