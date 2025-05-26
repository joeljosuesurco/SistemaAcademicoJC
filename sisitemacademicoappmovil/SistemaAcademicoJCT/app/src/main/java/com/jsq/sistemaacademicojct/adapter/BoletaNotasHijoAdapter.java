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

public class BoletaNotasHijoAdapter extends RecyclerView.Adapter<BoletaNotasHijoAdapter.ViewHolder> {

    private final List<NotaHijoResponse.Nota> notas;

    public BoletaNotasHijoAdapter(List<NotaHijoResponse.Nota> notas) {
        this.notas = notas;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View vista = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_boleta_hijo, parent, false);
        return new ViewHolder(vista);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        NotaHijoResponse.Nota nota = notas.get(position);
        holder.txtMateria.setText(nota.materia.sigla + " - " + nota.materia.area);
        holder.txtNotaFinal.setText("Nota Final: " + (nota.nota_final != null ? nota.nota_final : "N/R"));

        for (NotaHijoResponse.Dimension d : nota.dimensiones) {
            switch (d.nombre) {
                case "ser_docente":
                    holder.txtSer.setText(String.valueOf(d.valor));
                    break;
                case "saber_docente":
                    holder.txtSaber.setText(String.valueOf(d.valor));
                    break;
                case "hacer_docente":
                    holder.txtHacer.setText(String.valueOf(d.valor));
                    break;
                case "decidir_docente":
                    holder.txtDecidir.setText(String.valueOf(d.valor));
                    break;
                case "ser_autoeval":
                    holder.txtAutoSer.setText(String.valueOf(d.valor));
                    break;
                case "decidir_autoeval":
                    holder.txtAutoDecidir.setText(String.valueOf(d.valor));
                    break;
            }
        }
    }

    @Override
    public int getItemCount() {
        return notas.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView txtMateria, txtNotaFinal, txtSer, txtSaber, txtHacer, txtDecidir, txtAutoSer, txtAutoDecidir;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            txtMateria = itemView.findViewById(R.id.txtMateria);
            txtNotaFinal = itemView.findViewById(R.id.txtNotaFinal);
            txtSer = itemView.findViewById(R.id.txtSer);
            txtSaber = itemView.findViewById(R.id.txtSaber);
            txtHacer = itemView.findViewById(R.id.txtHacer);
            txtDecidir = itemView.findViewById(R.id.txtDecidir);
            txtAutoSer = itemView.findViewById(R.id.txtAutoSer);
            txtAutoDecidir = itemView.findViewById(R.id.txtAutoDecidir);
        }
    }
}
