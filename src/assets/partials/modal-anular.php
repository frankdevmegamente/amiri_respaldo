<!-- Componente genérico Anular Grupo (reutilizable) -->
<div class="modal fade" id="modalAnularGenerico" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger bg-opacity-10">
        <h5 class="modal-title text-danger"><i class="ti ti-alert-triangle"></i> Anular operación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">¿Seguro que deseas anular el grupo <strong id="anularGrupoLabel">#</strong>?</p>
        <div id="anularEfectos" class="small text-muted mb-2" style="display:none;"></div>
        <div class="mb-2">
          <label class="form-label">Motivo *</label>
          <textarea id="anularMotivo" class="form-control" rows="2" placeholder="Ej. Error de captura, duplicado..."></textarea>
        </div>
        <div class="alert alert-warning py-2 small mb-0">
          <i class="ti ti-info-circle"></i> Se crearán movimientos de reverso y se revertirán saldos. No se puede deshacer sin traza.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarAnularGenerico"><i class="ti ti-trash"></i> Confirmar anulación</button>
      </div>
    </div>
  </div>
</div>
