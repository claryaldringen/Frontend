$(document).ready ->
	if document.body.clientWidth > 1024
		number = Math.floor(Math.random()*100) % 2
		$('body').css('background-image', 'url(/images/background' + number + '.jpg)')

	changeImage = ->
		index = Math.round(Math.random()*100)%images.length
		$('#changing').attr('src','http://cms.freetech.cz/images/userimages/large/' + images[index])
		console.log index

	changeImage()
	window.setInterval(changeImage, 15 * 1000)