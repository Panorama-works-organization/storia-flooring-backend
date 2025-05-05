
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @font-face {
            font-family: 'neue-haas-unica';
            src: url({{ storage_path("fonts/NeueHaasUnica-Regular.ttf") }}) format("truetype");
            font-weight: 400;
            font-style: normal;
        }
         @font-face {
            font-family: 'neue-haas-unica';
            src: url({{ storage_path("fonts/NeueHaasUnica-Light.ttf") }}) format("truetype");
            font-weight: 100;
            font-style: light;
        }

        @font-face {
            font-family: 'Gt Super Display';
            src: url({{ storage_path("fonts/GT-Super-Display-Regular-Trial.otf") }}) format("truetype");
            font-weight: 400;
            font-style: italic;
        }
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
            /* font-family: sans-serif; */
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

    <section id="front-page" style="background-image: url({{$data['firstSlideImageURL']}}); background-size: cover; padding: 50px 50px 50px 50px;">
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
                <td style="color: white; font-size: xx-large; font-family:'Gt Super Display';">{{$data['catalogName']}}</td> 
                {{-- <td style="color: white; font-size: large; font-family: 'neue-haas-unica'; justify-content:end;">{{$data['catalogSubtitle']}}</td> --}}
            </tr>
        </table>
        <table style="width: 30%; height: 5%;">
            <tr align ="left" style="text-align: left;">
                <td style="width: 50%;color: white;font-family: 'neue-haas-unica';text-align: left;">CLIENT</td>
                <td style="width: 50%;color: white;font-family: 'neue-haas-unica';text-align: left;">DATE</td>
            </tr>
        </table>
        <table style="width: 30%; height: 75%;">
            <tr align ="left" style="text-align: left;">
                <td style="width: 50%;color: white; font-family: 'neue-haas-unica'; text-align: left;">{{$data['customerName']}}</td>
                <td style="width: 50%;color: white; font-family: 'neue-haas-unica';text-align: left;">{{$data['date']}}</td>
            </tr>
        </table>
    </section>

    <!--
    ****
        Pagina 2, iteracion de cada producto
        Hacer un foreach de cada producto con un array de datos cuando ande shopify
    ****
    -->
@foreach ($data['products'] as $product)

<section id="main-body" style="background: #EDECE6; background-size: cover; padding: 50px;">
    <table style="width: 100%; height: 10% 0 0 0;">
        <tr>
            <td style="text-align: center; vertical-align: middle;">
                <div style="display: flex; justify-content: center; align-items: center;">
                    <img src="https://i.imgur.com/f4Q6zJM.png" alt="product-image" width="160">
                </div>
            </td>
        </tr>
    </table>
    <table style="width: 100%; height: 90.7%; padding: 3.5% 0 0 0;">
        <tr>
            <td style="width: 40%; text-align: center; vertical-align: middle; padding-top: 65px;">
                <table style="margin: 0 auto;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="{{$product['image_url']}}" alt="product-image" width="300" height="300">
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: 'neue-haas-unica'; padding-top: 50px; font-size: xx-large; width: 300px; word-wrap: break-word;">
                            {{$product['title']}}
                        </td>
                    </tr>
                    <tr>
                        @if ($product['min_price'] === $product['max_price'])
                            <td style="width: 35%; height: 10% 0 0 0; text-align: start; vertical-align: top; font-size: x-large; font-family: 'neue-haas-unica';font-weight:100;">{{$product['max_price']}}</td>
                        @else
                            <td style="width: 35%; height: 10% 0 0 0; text-align: start; vertical-align: top; font-size: x-large; font-family: 'neue-haas-unica';font-weight:100;">{{$product['min_price']}} - {{$product['max_price']}}</td>
                        @endif
                        
                    </tr>
                </table>
            </td>
            <td style="width: 60%; vertical-align: bottom;">
                <table style="width: 100%; align-content: end;">

                    @foreach ($product['metafields'] as $metafield)
                    <tr>
                        <td><hr style="border-width: 0.5px; color: #262626;"></td>
                        <td><hr style="border-width: 0.5px; color: #262626;"></td>
                    </tr>
                        <tr style="align-items: center;">
                            <td style="font-family: 'neue-haas-unica'; font-weight:100; padding: 8px 0 8px 0; align-items: center;">{{$metafield['key']}}</td>
                            <td style="text-align: right; font-family: 'neue-haas-unica'; font-weight:100; padding: 8px 0 8px 0; align-items: center;">
                                @if ( is_array($metafield['value']))
                                    {{implode(', ', $metafield['value'])}}
                                @else
                                    {{$metafield['value']}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($product['options'])
                            @foreach ($product['options'] as $option)
                                @if($option['name'] != 'TITLE')
                                <tr>
                                    <td><hr style="border-width: 0.5px; color: #262626;"></td>
                                    <td><hr style="border-width: 0.5px; color: #262626;"></td>
                                </tr>
                                    <tr style="align-items: center;">
                                        <td style="font-family: 'neue-haas-unica'; font-weight:100; padding: 8px 0 8px 0; align-items: center;">{{$option['name']}}</td>
                                        <td style="text-align: right; font-family: 'neue-haas-unica'; font-weight:100; padding: 8px 0 8px 0; align-items: center;">{{$option['values']}}</td>
                                    </tr>
                                @endif
                            @endforeach
                    @endif
                    <tr>
                        <td><hr style="border-width: 0.5px; color: #262626;"></td>
                        <td><hr style="border-width: 0.5px; color: #262626;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</section>


@endforeach
    <!--
    ****
        Pagina 3
        Ya esta lista, faltaria la fuente
    ****
    -->

    <section id="back-page" style="background: #262626; background-size: cover; padding: 30px;heigth: 100vh;">
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
        <p style="color: white; text-align: center; justify-content: center; font-size: 40px; margin: 40px 0 0 0; font-family:'Gt Super Display';">The perfect</p>
        <p style="color: white; text-align: center; justify-content: center; font-size: 40px; font-family:'Gt Super Display';">flooring solutions.</p>
        <hr style="height 4px; background-color: white; margin: 185px 0 0 0;">
        <table style="width: 100%; heigth: 100%; margin: 25px 0 0 0;">
            <tr>
                <td style="color: white; font-family: 'neue-haas-unica';">© {{now()->year}} Storia Flooring. All rights reserved.</td>
                <td style="color: white; text-align: right;">
                    <a style="color: white; text-decoration:none; font-family: 'neue-haas-unica';" href="https://storiaflooring.com/" target="_blank">@StoriaFlooring</a>
                </td>
                <td style="color: white; text-align: right;">
                    <a style="color: white; text-decoration:none; font-family: 'neue-haas-unica';" href="https://storiaflooring.com/" target="_blank">www.storiaflooring.com</a>
                </td>
            </tr>
        </table>
    </section>
</body>
</html>