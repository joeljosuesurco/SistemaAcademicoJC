package com.jsq.sistemaacademicojct.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.jsq.sistemaacademicojct.R;
import com.jsq.sistemaacademicojct.model.NotaHijoResponse;

import java.util.List;

public class NotaHijoAdapter extends RecyclerView.Adapter<NotaHijoAdapter.NotaViewHolder> {

    private final List<NotaHijoResponse.Nota> notas;

    public NotaHijoAdapter(List<NotaHijoResponse.Nota> notas) {
        this.notas = notas;
    }

    @NonNull
    @Override
    public NotaViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View vista = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_nota_hijo, parent, false);
        return new NotaViewHolder(vista);
    }

    @Override
    public void onBindViewHolder(@NonNull NotaViewHolder holder, int position) {
        NotaHijoResponse.Nota nota = notas.get(position);
        holder.txtMateria.setText(nota.materia.sigla + " - " + nota.materia.area);
        holder.txtNotaFinal.setText("Nota Final: " + (nota.nota_final != null ? nota.nota_final : "N/R"));

        StringBuilder dimensionesText = new StringBuilder("Dimensiones:\n");
        for (NotaHijoResponse.Dimension d : nota.dimensiones) {
            dimensionesText.append("- ").append(d.nombre).append(": ").append(d.valor).append("\n");
        }

        holder.txtDimensiones.setText(dimensionesText.toString().trim());
    }

    @Override
    public int getItemCount() {
        return notas.size();
    }

    static class NotaViewHolder extends RecyclerView.ViewHolder {
        TextView txtMateria, txtNotaFinal, txtDimensiones;

        public NotaViewHolder(@NonNull View itemView) {
            super(itemView);
            txtMateria = itemView.findViewById(R.id.txtMateria);
            txtNotaFinal = itemView.findViewById(R.id.txtNotaFinal);
            txtDimensiones = itemView.findViewById(R.id.txtDimensiones);
        }
    }
}
