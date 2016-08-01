jQuery(document).ready(function($) {
		showSnapshot = function() {
			$('#snapshot_btn').css('display', 'none');
			$('#my_camera').css('display', 'block');
			$('#take_snapshot').css('display', 'block');
			$('#set-post-thumbnail').css('display', 'none');
			$('#remove-post-thumbnail').css('display', 'none');
			$('#snapshot_cancel').css('display', 'block');
			
			if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
				Webcam.set({
					width: 230,
					height: 150,
					image_format: 'jpeg',
					jpeg_quality: 90,
					force_flash: true
				})
			} else {
				Webcam.set({
					width: 230,
					height: 150,
					image_format: 'jpeg',
					jpeg_quality: 90,
					force_flash: false
				});
			}
			Webcam.attach( '#my_camera' );
		}

		take_snapshot = function() {
			Webcam.snap( function(data_uri) {
				$('#my_camera').css('display', 'none');
				$('#take_snapshot').css('display', 'none');
				$('input[name="webcam_image"]').val(data_uri);
				$('#snap_image').attr('src', data_uri);
				$('#snap_image').css('display', 'block');
				$('#snapshot_reset').css('display', 'block');
			} );
		}

		reset_webcam = function() {
			$('#snap_image').attr('src', "");
			$('input[name="webcam_image"]').val("");
			$('#snap_image').css('display', 'none');
			$('#snapshot_reset').css('display', 'none');
			$('#my_camera').css('display', 'block');
			$('#take_snapshot').css('display', 'block');
		}

		snapshot_cancel = function() {
			$('#snap_image').attr('src', "");
			$('input[name="webcam_image"]').val("");
			$('#snap_image').css('display', 'none');
			$('#snapshot_btn').css('display', 'block');
			$('#snapshot_reset').css('display', 'none');
			$('#take_snapshot').css('display', 'none');
			$('#snapshot_cancel').css('display', 'none');
			$('#my_camera').css('display', 'none');
			$('#set-post-thumbnail').css('display', 'block');
			$('#remove-post-thumbnail').css('display', 'block');
			Webcam.reset();
		}

		WPSetThumbnailHTML = function(html){
			$('.featured-image-client', '#postimagediv').html(html);
		};

		wp.media.featuredImage.set = function( id ) {
			var settings = wp.media.view.settings;

			settings.post.featuredImageId = id;

			wp.media.post( 'set-post-thumbnail', {
				json:         true,
				post_id:      settings.post.id,
				thumbnail_id: settings.post.featuredImageId,
				_wpnonce:     settings.post.nonce
			}).done( function( html ) {
				$('.featured-image-client', '#postimagediv').html(html);
			});
		}


		function CPcuitValido(cuit) {
			if(cuit == ''){
				return true;
			}
			if (!(cuit.match(/^\d{2}([\-_])?\d{8}([\-_])?\d{1}$/))) {
				return false;
			}
			cuit = cuit.toString().replace(/[-_]/g, '');
			var vec = new Array(10);
			var esCuit = false;
			var cuit_rearmado = '';
			for (i=0; i < cuit.length; i++) {
				caracter = cuit.charAt( i);
				if ( caracter.charCodeAt(0) >= 48 && caracter.charCodeAt(0) <= 57 )     {
					cuit_rearmado += caracter;
				}
			}
			cuit=cuit_rearmado;
			if ( cuit.length != 11) {  // si no estan todos los digitos
				esCuit=false;
			} else {
				x=i=dv=0;
				// Multiplico los dÃ­gitos.
				vec[0] = cuit.charAt(0) * 5;
				vec[1] = cuit.charAt(1) * 4;
				vec[2] = cuit.charAt(2) * 3;
				vec[3] = cuit.charAt(3) * 2;
				vec[4] = cuit.charAt(4) * 7;
				vec[5] = cuit.charAt(5) * 6;
				vec[6] = cuit.charAt(6) * 5;
				vec[7] = cuit.charAt(7) * 4;
				vec[8] = cuit.charAt(8) * 3;
				vec[9] = cuit.charAt(9) * 2;

				// Suma cada uno de los resultado.
				for( i = 0;i<=9; i++) {
					x += vec[i];
				}
				dv = (11 - (x % 11)) % 11;
				if (dv == cuit.charAt(10) ) {
					esCuit=true;
				}
			}
			if ( !esCuit ) {
				return false;
			}
			return true;
		}
		$('#taxpayer').keyup(function(){
			if(this.value==''){
				$('#cuit_validation').text('');
			} else if(!CPcuitValido(this.value)){
				$('#cuit_validation').text('Invalid cuit').removeClass('cuit_ok').addClass('cuit_err');
			} else {
				$('#cuit_validation').text('Cuit OK').removeClass('cuit_err').addClass('cuit_ok');
			}
		});
	});