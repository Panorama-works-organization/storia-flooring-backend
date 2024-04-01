
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
       
        *{
            font-family: "N 27",Impact,sans-serif;
            margin: 0;
            padding: 0;
        }
        h1,h2,h3,h4,h5,h6,p{
            font-family: "N 27",Impact,sans-serif;
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
    <section id="portrait">
        <div style="padding: 30px 40px; position:absolute;">
            <img style="width: 250px" src="{{ storage_path('do-logo-white.png')}}" alt="">
        </div>
        <div>
            <img style="width:1344px;height:511px;object-fit:cover;" src="{{$data['firstSlideImageURL']}}" alt="">
            <div style="width:100%;height:30%;padding:0 60px;">
                <table style="width:100%;height:100%;vertical-align: middle;">
                    <tr style="margin-top:20px;">
                        <td style="width:350px;vertical-align: middle;padding-right:50px;">
                            <p style="font-size:65px;">
                                {{$data['catalogName']}}
                            </p>
                        </td>
                        <td style="width:250px;vertical-align: middle;">
                            <table style="width:100%;vertical-align: middle;">
                                <tr>
                                    <td style="width:50%;vertical-align: middle;">
                                        <div>
                                            <p style="font-size:10px;">CLIENT</p>
                                            <p style="font-size:14px;">{{$data['customerName']}}</p> 
                                        </div>
                                    </td>
                                    <td style="width:50%;vertical-align: middle;">
                                         <div>
                                            <p style="font-size:10px;">DATE</p>
                                            <p style="font-size:14px;">{{$data['date']}}</p> 
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </section>
    @foreach ($data['products'] as $product)
        @foreach ($product['variants'] as $variant)
        <section class="products" style="height:2480px;width:3508px; background-color:#FFF; padding:40px 30;">
        <table >
            <thead>
                <tr>
                    <td>
                        <p style="font-size:28px; line-height:0.7; margin-bottom:40px;">
                            @if($variant['title'] === 'Default Title')
                                @php
                                    $slideTitle = $product['title'];
                                @endphp
                            @else
                                @php
                                    $slideTitle = $product['title'] . ': ' . $variant['title'];
                                @endphp
                            @endif 
                            {{$slideTitle}}<br><br>
                            <span style="font-size:20px;">{{$variant['sku']}}</span> <br>
                            <span style="font-size:20px;">$ {{number_format($variant['price'],2)}} MXN</span>
                        </p>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td style="padding-right:60px;">
                                        <div 
                                        style="width:280px;height:350px;background-image:url('{{$variant['image_src']}}');background-position: center;background-repeat: no-repeat;">
                                    </div>
                                        
                                    </td>
                                    <td style="border-top:solid 1px #000;width:300px;">
                                        <table>
                                            <tbody>
                                                <tr>
                                                <td style="width:400px;">
                                                    <p style="font-size:14px;padding-top:30px;margin-bottom:50px;">DESCRIPTION</p>
                                                    <div style="font-size:12px;margin-bottom:50px;">
                                                        {!! $product['description'] !!}
                                                    </div>
                                                    <div style="position: absolute; bottom:121px;">
                                                        <a target="_blank" href="https://danielorozcoestudio.com/products/{{$product['handle']}}" style="background-color:#000;text-transform:uppercase;padding:10px 35px;color:#fff; font-size:12px;text-decoration:none;box-shadow: 0px 4px 4px 1px #00000038;line-height: 35px;">
                                                            VIEW MORE
                                                        </a>
                                                    </div>
                                                </td>
                                                <td style="width:250px;text-align:center;">
                                                    <div style="position: absolute; bottom:120px; right:80px">
                                                        @if ($variant['variant_plan'] != "")
                                                            <p style="font-size:14px; margin-bottom:20px;">VARIANT PLAN</p>
                                                            <img style="width:150px;"   src="{{$variant['variant_plan']}}" alt="">
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
        @endforeach
    @endforeach
    <section id="backover" style="">
        <div style="background-color:#cfcbc0;height:100vh; padding:50px 35px;">
            <div style="width:100%;height:100%;">
                <img style="width: 100%" src="https://danielorozcoestudio.com/cdn/shop/files/Logo-Daniel-Orozco_2048x.svg?v=1698297160" alt="">
                <div style="position: absolute; bottom:30px; left:0;padding:0 30px;">
                    <table>
                        <tbody>
                            <tr>
                                <td style="width:100px;">
                                    <a style="color:#000;text-decoration:none;margin-right:30px;" href="https://danielorozcoestudio.com/" target="_blank">danielorozco.com</a>
                                </td>
                                <td style="width:100px;">
                                    <a style="color:#000;text-decoration:none;" href="danielorozco.com">@danielorozcostudio</a></td>
                                <td style="width:750px;text-align:right;">
                                    <p>© {{now()->year}}. All rights reserved.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div>
                        
                        
                    </div>
                    <div>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </section>
</body>
</html>