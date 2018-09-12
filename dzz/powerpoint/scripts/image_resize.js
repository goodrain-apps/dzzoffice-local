	//弹窗缩略图格式
	function image_resize(img, scale) {
			// console.log(img);
			
			width = parseInt(jQuery(img).width());//this.width;
			height = parseInt(jQuery(img).height());//this.height;
			p_width = jQuery(img).parent().width();
			p_height = jQuery(img).parent().height();
			if(jQuery(img).closest('.box').length){
				p_width = jQuery(img).closest('.box').width();
				p_height = jQuery(img).closest('.box').height();
			}
			var r = width / height;
			var r1 = p_width / p_height;
			if(r > r1) {
				jQuery(img).css({
					'max-height': '100%',
					'max-width': 'none'
				});
				width = parseInt(jQuery(img).width());
				height = parseInt(jQuery(img).height());			
				if(height > p_height) {
					jQuery(img).css({
						'margin-left': (p_width - width * (p_height / height)) / 2 + 'px',
						'margin-top': 0
					});
				} else {
					jQuery(img).css({
						'margin-left': (p_width - width) / 2 + 'px',
						'margin-top': (p_height - height) / 2 + 'px',
					});
				}
			} else if(r < r1) {
				jQuery(img).css({
					'max-width': '100%',
					'max-height': 'none'
				});
				width = parseInt(jQuery(img).width());
				height = parseInt(jQuery(img).height());
				if(width>=p_width) {
					jQuery(img).css({
						'margin-top': (p_height - height * (p_width / width)) / 2 + 'px',
						'margin-left': 0
					});
				} else {
					
					jQuery(img).css({
						'margin-left': (p_width - width) / 2 + 'px',
						'margin-top': (p_height - height) / 2 + 'px'
					});
				}
			} else {
				jQuery(img).css({
					'max-width': '100%',
					'max-height': '100%',
					'margin-left': (p_width > width ? (p_width - width) / 2 : 0) + 'px',
					'margin-top': (p_height > height ? (p_height - height) / 2 : 0) + 'px'
				});
			}
	}