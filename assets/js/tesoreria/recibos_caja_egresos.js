$(document).ready(function(){

	$('#teso_caja_id').parent().parent().hide();
	$('#teso_cuenta_bancaria_id').parent().parent().hide();

  var valor = $('#teso_medio_recaudo_id').val().split('-');
  if ( valor[1] == 'Tarjeta bancaria' )
  {
    $('#teso_cuenta_bancaria_id').parent().parent().fadeIn();
    $('#teso_cuenta_bancaria_id').attr('required','required');
  }else{
    $('#teso_caja_id').parent().parent().fadeIn();
    $('#teso_caja_id').attr('required','required');
  }

	$('#teso_medio_recaudo_id').change(function(){
		var valor = $(this).val().split('-');
		if (valor!='') {
			if (valor[1]=='Tarjeta bancaria'){
				$('#teso_caja_id').parent().parent().fadeOut();
        $('#teso_caja_id').removeAttr('required');

				$('#teso_cuenta_bancaria_id').parent().parent().fadeIn();
        $('#teso_cuenta_bancaria_id').attr('required','required');
			}else{
				
        $('#teso_cuenta_bancaria_id').parent().parent().fadeOut();
        $('#teso_cuenta_bancaria_id').removeAttr('required');

				$('#teso_caja_id').parent().parent().fadeIn();
        $('#teso_caja_id').attr('required','required');
			}
		}else{
			$('#teso_cuenta_bancaria_id').parent().parent().fadeOut();
      $('#teso_cuenta_bancaria_id').removeAttr('required');

			$('#teso_caja_id').parent().parent().fadeOut();
      $('#teso_caja_id').removeAttr('required');
			$(this).focus();
		}
	});

	CKEDITOR.replace('documento_soporte', {
		toolbar: [{
          name: 'clipboard',
          items: ['PasteFromWord', '-', 'Undo', 'Redo']
        },
        {
          name: 'basicstyles',
          items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Subscript', 'Superscript']
        },
        {
          name: 'links',
          items: ['Link', 'Unlink']
        },
        {
          name: 'paragraph',
          items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
        },
        {
          name: 'insert',
          items: ['Image', 'Table']
        },
        {
          name: 'editing',
          items: ['Scayt']
        },
        '/',

        {
          name: 'styles',
          items: ['Format', 'Font', 'FontSize']
        },
        {
          name: 'colors',
          items: ['TextColor', 'BGColor', 'CopyFormatting']
        },
        {
          name: 'align',
          items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
        },
        {
          name: 'document',
          items: ['Print', 'Source']
        }
      ],

      // Enabling extra plugins, available in the full-all preset: https://ckeditor.com/cke4/presets
      extraPlugins: 'colorbutton,font,justify,print,tableresize,pastefromword,liststyle',
      removeButtons: '',
	  height: 200
	});
});



function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
{
  $('#core_tercero_id').before('<input type="hidden" id="vehiculo_id" name="vehiculo_id" value="' + item_sugerencia.attr('data-vehiculo_id') + '" >');
}