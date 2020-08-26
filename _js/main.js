var texto = document.getElementById('code'),
  menu = document.getElementById('menu');
  botoes  = document.getElementById('botoes'),
  principal = document.getElementById('principal');

var btnMenu = ['baixar', 'visualizar'];

document.getElementById('comecar').onclick = function() {
  texto.classList.add('texto');
  texto.style.visibility = 'visible';
  principal.style.display = 'none';
  botoes.style.display = 'block';
}

document.getElementById('cancelar').onclick = function() {
  var i = 0, opacity = 1, width = screen.width, height = 300;

  var efeito = setInterval(function() {
    i += 1;

    if (i <= 400) {
      if (i % 25 == 0) {
        width -= 194
        height -= 27.1;
        botoes.style.opacity = opacity -= 0.07125;
        texto.style.width = width + "px";
        texto.style.height = height + "px";
      }
    } else {
      botoes.style.display = 'none';
      texto.style.visibility = 'hidden';
      menu.style.display = 'block';
      clearInterval(efeito);
    }
  }, 1);
}


for (let i = 0; i < 2; i++) {
  document.getElementById(btnMenu[i]).onclick = function () {
    menu.style.display = 'none';
    principal.style.display = 'block';
  }
}
