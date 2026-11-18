/**
 * anular.js - Función genérica para anular cualquier grupo_id
 * Usa api/movimientos-correccion.php?action=anular (servicio central)
 * Requiere: jQuery, bootstrap, showToast global
 */
(function(){
  let grupoPendiente = null;

  window.abrirAnularGenerico = function(grupoId, opts){
    opts = opts || {};
    grupoPendiente = grupoId;
    $('#anularGrupoLabel').text('#'+grupoId);
    $('#anularMotivo').val(opts.motivo || '');
    $('#anularEfectos').hide().empty();
    $('#modalAnularGenerico').modal('show');
    // Opcional: cargar efectos previos
    if (opts.mostrarEfectos !== false) {
      $.getJSON('api/movimientos-correccion.php?action=detalle&grupo_id='+grupoId, function(res){
        if(res && res.grupo){
          let html='<div class="border rounded p-2 bg-light">Movimientos en grupo: '+(res.movimientos?res.movimientos.length:1)+'</div>';
          $('#anularEfectos').html(html).show();
        }
      }).fail(function(){});
    }
  };

  // Delegación genérica: cualquier botón con .btn-anular-generico data-grupo
  $(document).on('click', '.btn-anular-generico', function(){
    const g = $(this).data('grupo') || $(this).data('grupo_id');
    if(!g) return showToast('Grupo no especificado','error');
    abrirAnularGenerico(g);
  });

  $(document).on('click', '#btnConfirmarAnularGenerico', function(){
    const motivo = $('#anularMotivo').val().trim();
    if(!motivo) return showToast('Motivo obligatorio','error');
    if(!grupoPendiente) return showToast('Grupo no seleccionado','error');
    const $btn = $(this);
    $btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span> Anulando...');
    $.ajax({
      url: 'api/movimientos-correccion.php',
      method: 'POST',
      contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
      data: $.param({action:'anular', grupo_id: grupoPendiente, motivo: motivo}),
      success: function(res){
        $('#modalAnularGenerico').modal('hide');
        showToast(res.message || 'Anulado correctamente','success');
        // Recargar tablas comunes si existen
        try{ if(window.table && table.ajax) table.ajax.reload(); }catch(e){}
        try{ if(window.tabla && tabla.ajax) tabla.ajax.reload(); }catch(e){}
        // Fallback reload
        setTimeout(()=> location.reload(), 800);
      },
      error: function(xhr){
        let msg='Error al anular';
        try{ msg=JSON.parse(xhr.responseText).error || msg; }catch(e){ msg=xhr.responseText||msg; }
        showToast(msg,'error');
      },
      complete: function(){ $btn.prop('disabled',false).html('<i class="ti ti-trash"></i> Confirmar anulación'); }
    });
  });
})();
