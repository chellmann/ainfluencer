<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1080, initial-scale=1.0">
    <title>Bewegter Hintergrund</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            height: 1920px;
            background-image: url(/storage/{{ $post->image }});
            /* Ersetze 'dein-bild.png' mit dem Pfad zu deinem Bild */
            background-size: cover;
            /*animation: moveBackground 40s linear infinite;*/
            display: flex;
            justify-content: center;
            align-items: flex-end; /* Positioniert den Inhalt am unteren Ende */
        }

        @keyframes moveBackground {
            0% {
                background-position: 30% 0%;
            }

            100% {
                background-position: 70% 0%;
            }
        }

        .scale__container--js {
        text-align: center;
        }
        .scale--js {
        display: inline-block;
        transform-origin: 50% 0;
        -webkit-font-smoothing: antialiased;
        transform: translate3d( 0, 0, 0);
        }

        .text-block {
            color: {!! Mateffy\Color::hex($post->font_color)->toHexString() !!}; /* Weißer Text */
            padding: 20px; /* Innenabstand */
            margin-bottom: 20%; /* Abstand vom unteren Rand */
            width: 80%; /* Breite des Textblocks */
            text-align: center; /* Text zentrieren */
            font-size: 2em; /* Schriftgröße */
            font-family: 'Arial', sans-serif; /* Schriftart */
            font-weight: bold; /* Fettdruck */
            text-shadow:
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!},
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!},
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!},
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!},
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!},
                    0px 0px 20px {!! Mateffy\Color::hex($post->font_color)->invert()->toHexString() !!}; /* Schwarze Outline */
        }
    </style>
</head>

<body>

    <script>
        function scaleHeader() {
            var scalable = document.querySelectorAll('.scale--js');
            var margin = 10;
            for (var i = 0; i < scalable.length; i++) {
                var scalableContainer = scalable[i].parentNode;
                scalable[i].style.transform = 'scale(1)';
                var scalableContainerWidth = scalableContainer.offsetWidth - margin;
                var scalableWidth = scalable[i].offsetWidth;
                scalable[i].style.transform = 'scale(' + scalableContainerWidth / scalableWidth + ')';
                scalableContainer.style.height = scalable[i].getBoundingClientRect().height + 'px';
            }
        }

        scaleHeader();
        window.addEventListener('resize', scaleHeader);
        window.addEventListener('load', scaleHeader);
        let posX = 10;
        let posY = 0;
        const speed = 0.5; // Geschwindigkeit der Animation

        function animateBackground() {
            posX -= speed;
            //posY += speed;

            // Setze die Hintergrundposition
            document.body.style.backgroundPosition = `${posX}px ${posY}px`;

            // Wiederhole die Animation
            requestAnimationFrame(animateBackground);
        }

        // Starte die Animation
        animateBackground();
    </script>


        <div class="text-block">
            <div class="scale__container--js">
                <p class="scale--js">{!! nl2br($post->text) !!}
                    @if ($post->author)
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - {{ $post->author }}
                    @endif
                </p>
            </div>
        </div>
</body>

</html>
