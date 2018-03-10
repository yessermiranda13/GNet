<input type="hidden" id="title_sm" value="" />
<input type="hidden" id="content_sm" value="" />

<?php
  //include ("ic.test.php");

  #Agregando ventana modal, configuración de la red.
  include (PD_DESKTOP_ROOT."/graphic/ic.modal.config_network.php");
  include (PD_DESKTOP_ROOT."/graphic/gn.modal.AddDevicesManagement.php");
?>

<div class="container_platform">
    <label class="field prepend-icon">
                    <input type="text" name="tooltip2" id="tooltip2" class="gui-input" placeholder="Left">
                     <b class="tooltip tip-left"><em> I am a left aligned tooltip!</em></b>
                    <label for="tooltip2" class="field-icon"><i class="fa fa-flag"></i>
                    </label>
                </label>

                <label class="option option-danger">
                              <input name="checked" value="checked" checked="" type="checkbox">
                              <span class="checkbox"></span>Check</label>
</div>


<button type="hidden" class="AddRedactDocumentation" data-toggle="modal" data-target="#NowAddRedactDocumentation"></button>

<!-- <!- Modal -->
<div class="modal fade" id="NowAddRedactDocumentation" tabindex="-1" role="dialog" aria-labelledby="ModalRedactionDocument" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="ModalRedactionDocument">Redactar el documento</h4>
      </div>
      <div class="modal-body">
	
			<script src="app/controller/src/plugins/ckeditor/ckeditor.js"></script>
			<script src="app/controller/src/plugins/ckeditor/samples/js/sample.js"></script>
			<link href="app/controller/src/plugins/ckeditor/plugins/codesnippet/lib/highlight/styles/monokai_sublime.css" rel="stylesheet">

			<div class="adjoined-bottom">
				<div class="grid-container">
					<div class="grid-width-100">
						<div id="editor">
							<h1>¡Escribe tu documento!</h1>
						</div>
					</div>
				</div>
			</div>

			<script>
				initSample();
			</script>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-lg btn-primary savechange" data-placement="bottom" data-dismiss="" data-toggle="popover" title="Mensaje de acción" data-content="Los cambios han sido guardados con éxito!.">Guardar cambios</button>

      </div>
    </div>
  </div>
</div>