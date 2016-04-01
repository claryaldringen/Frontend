class @Banner

	constructor: ->
		@image = 1
		window.setInterval =>
			$('#banner-image').fadeOut 2000, =>
				@image++
				@image = 0 if @image > 7
				@render()
		, 6000

	getHtml: ->
		image2 = @image + 1
		image2 = 1 if image2 > 7
		html = '<div class="bckg' + image2 + '">'
		html += '<div id="banner-image" class="bckg' + @image + '">'
		html

	render: ->
		element = document.getElementById('banner')
		element.innerHTML = @getHtml()


