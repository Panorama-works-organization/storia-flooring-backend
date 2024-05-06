
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
       
        *{
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed, 
        figure, figcaption, footer, header, hgroup, 
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            vertical-align: baseline;
        }
        /* HTML5 display-role reset for older browsers */
        article, aside, details, figcaption, figure, 
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        body {
            line-height: 1;
            font-family: sans-serif;
        }
        @font-face {
	        font-family: 'PlayfairDisplay-Medium';
	        font-style: normal;
	        font-weight: normal;
	        src: url("../storage/fonts/PlayfairDisplay-Medium.otf") format('opentype');
	    }
        @font-face {
	        font-family: 'Arimo-Regular';
	        font-style: normal;
	        font-weight: normal;
	        src: url("../storage/fonts/Arimo-Regular.otf") format('opentype');
	    }
        ol, ul {
            list-style: none;
        }
        blockquote, q {
            quotes: none;
        }
        blockquote:before, blockquote:after,
        q:before, q:after {
            content: '';
            content: none;
        }
        table, tr, th, td{
            border-collapse: collapse;
            border-spacing: 0;
            border: none !important;
        }
        /* tr, td{
            border:dotted 1px #000;
        } */
        
    </style>
</head>
<body>

    <!--
    ****
        Pagina 1
        Cuando ande Shopify, pasarle las variables de: Titulo, subtitulo, cliente, fecha actual y imagen con gradiente
    ****
    -->

    <section id="front-page" style="background-image: url(https://i.imgur.com/sbZe94O.jpeg); background-size: cover; padding: 50px 50px 50px 50px;">
        <table style="width: 100%; height: 10%;">
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <img src="https://i.imgur.com/glABUv1.png" alt="product-image" width="160">
                    </div>
                </td>
            </tr>
        </table>
        <table style="width: 100%; height: 10%;">
            <tr>
                <td style="color: white; font-size: xx-large; font-weight: bold; font-family:'Arimo-Regular';">Titulo de PRUEBA</td> 
                <td style="color: white; font-size: large; font-family: 'PlayfairDisplay-Medium'; justify-content:end;">Subtitulo de PRUEBA</td>
            </tr>
        </table>
        <table style="width: 30%; height: 5%;">
            <tr>
                <td style="color: white;">CLIENT</td>
                <td style="color: white;">DATE</td>
            </tr>
        </table>
        <table style="width: 27%; height: 75%;">
            <tr>
                <td style="color: white; font-family: 'PlayfairDisplay-Medium';">Gaston Corredoira</td>
                <td style="color: white; font-family: 'PlayfairDisplay-Medium';">05/05/2024</td>
            </tr>
        </table>
    </section>

    <!--
    ****
        Pagina 2, iteracion de cada producto
        Hacer un foreach de cada producto con un array de datos cuando ande shopify
    ****
    -->

    <section id="main-body" style="background: #EDECE6; background-size: cover; padding: 50px;">
        <table style="width: 100%; height: 10%;">
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <img src="https://i.imgur.com/f4Q6zJM.png" alt="product-image" width="160">
                    </div>
                </td>
            </tr>
        </table>
        <table style="width: 100%; height: 90%;">
            <tr>
                <td style="width: 40%; height: 50%; text-align: center; vertical-align: middle;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <img src="https://www.cienciasinseso.com/wp-content/uploads/2013/08/estudias.jpg" alt="product-image" width="300" height="300">
                    </div>
                </td>
                <td style="width: 60%;">
                    <table style="width: 100%; margin: 40px 0 0 0;">
                    </table>
                    <table style="width: 100%;">
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 0">MATERIAL</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>ORIGIN</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>SPECIES</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>COLLECTION</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
                    </table>
                    <p style="font-size: x-large; margin: 50px 0 10px 0">Specifications</p>
                    <table style="width: 100%;">
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
                        <tr>
                            <td>FINISH</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>PATTERN</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>SURFACE TEXTURE</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>MILLING PROFILE</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>EDGE PROFILE</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>GRADE</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>WEAR LEVER</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>THICKNESS</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>WIDTH</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>LENGHT</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>INSTALLATION METHOD</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td>WARRANTY</td>
                            <td style="text-align: right">Hola</td>
                        </tr>
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 35%; height: 10%; text-align: center; vertical-align: top; font-size: xx-large">Prueba producto</td>
            </tr>
        </table>
    </section>
    
    <!--
    ****
        Pagina 3
        Ya esta lista, faltaria la fuente
    ****
    -->

    <section id="back-page" style="background: #262626; background-size: cover; padding: 30px;">
        <p style="font-size: x-large; margin: 80px 0 30px 0"> </p>
        <table style="width: 100%; heigth: 100%;">
            <tr style="width: 100%; heigth: 90%; text-align: center; vertical-align: middle;">
                <td style="width: 100%; heigth: 90%; text-align: center; vertical-align: middle;">
                    <div style="display: flex; justify-content: center; align-items: center; margin 50px;">
                        <img src="https://i.imgur.com/MVjxgfk.jpeg" width="150">
                    </div>
                </td>
            </tr>
        </table>
        <p style="color: white; text-align: center; justify-content: center; font-size: 40px; margin: 40px 0 0 0;">The perfect</p>
        <p style="color: white; text-align: center; justify-content: center; font-size: 40px;">flooring solutions.</p>
        <hr style="height 4px; background-color: white; margin: 196px 0 0 0;">
        <table style="width: 100%; heigth: 100%; margin: 25px 0 0 0;">
            <tr>
                <td style="color: white;">© {{now()->year}} Storia Flooring. All rights reserved.</td>
                <td style="color: white; text-align: right;">
                    <a style="color: white; text-decoration:none;" href="https://storiaflooring.com/" target="_blank">@StoriaFlooring</a>
                </td>
                <td style="color: white; text-align: right;">
                    <a style="color: white; text-decoration:none;" href="https://storiaflooring.com/" target="_blank">www.storiaflooring.com</a>
                </td>
            </tr>
        </table>
    </section>
</body>
</html>