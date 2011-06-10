/**
 * AJAX uploader initialization
 */
$(function(){
	
	var uploader = new qq.FileUploader({
		// pass the dom node (ex. $(selector)[0] for jQuery users)
		element: document.getElementById('homepage-upload-link'),
		// path to server-side upload script
		action: '/upload',
		allowedExtensions: ['jpg', 'JPG', 'jpeg', 'png', 'gif'],
		sizeLimit: 20000000, // max size   
		minSizeLimit: 1000, // min size 
		onComplete: function(id, fileName, responseJSON) {
			
			if (responseJSON.success) {
				location.href = "/upload/report";
				return;
			}
			
			alert('Помилка завантаження файлу! Спробуйте пізніше або залиште відгук про цю помилку.')

			/*
			$('#file-uploader').html('').after(
			  '<p> Source language: <b>' + responseJSON.currentLang + '</b></p>'
			+ '<p id="translations">Click to download translated files:</br>' + '</p>');
			
			$.each(responseJSON.availLangs, function(index, value) {
				$('#translations').append('<div>' + value + ': <a href="/result/get/lang/' + index + '" target="_blank">strings.xml</a></div>');
			});

			/*
			$('#translations').after('<select id="more-langs"><option value="0">[ more languages ]</option>' 
			+ '<option value="jp">Japanese</option>'
			+ '<option value="jp">Korean</option>'
			+ '<option value="jp">Dutch</option>'
			+ '<option value="jp">Turkish</option>'
			+ '<option value="jp">Polish</option>'
			+ '<option value="jp">Swedish</option>'
			+ '<option value="jp">Norwegian</option>'
			+ '<option value="jp">Greek</option>'
			+ '<option value="jp">Czech</option>'
			+ '<option value="jp">Hebrew</option>'
			+ '<option value="jp">Danish</option>'
			+ '<option value="jp">Finnish</option>'
			+ '<option value="jp">Romanian</option>'
			+ '<option value="jp">Hungarian</option>'
			+ '<option value="jp">Vietnamese</option>'
			+ '<option value="jp">Punjabi</option>'
			+ '<option value="jp">Slovak</option>'
			+ '<option value="jp">Hindi</option>'
			+ '<option value="jp">Bulgarian</option>'
			+ '<option value="jp">Serbian</option>'
			+ '</select>');
						
			$('#more-langs').change(function () {
				alert('Sorry! This feature is still not implemented :) . Ask Joseph to finish this.');
			});
			*/
		},
	});
})
