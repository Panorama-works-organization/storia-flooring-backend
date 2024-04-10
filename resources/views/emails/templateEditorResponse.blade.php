<!DOCTYPE html>
<html lang="es">

<head>
  <title>Solicitud de editor</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width">
  <style>
    body {
      margin: 0;
      background-color: #f0f0f0 !important;
    }

    h1 a:hover {
      font-size: 30px;
      color: #333;
    }

    h1 a:active {
      font-size: 30px;
      color: #333;
    }

    h1 a:visited {
      font-size: 30px;
      color: #333;
    }

    a:hover {
      text-decoration: none;
    }

    a:active {
      text-decoration: none;
    }

    a:visited {
      text-decoration: none;
    }

    .button__text:hover {
      color: #fff;
      text-decoration: none;
    }

    .button__text:active {
      color: #fff;
      text-decoration: none;
    }

    .button__text:visited {
      color: #fff;
      text-decoration: none;
    }

    a:hover {
      color: #101010;
    }

    a:active {
      color: #101010;
    }

    a:visited {
      color: #101010;
    }

    @media (max-width: 600px) {
      .container {
        width: 94% !important;
      }

      .main-action-cell {
        float: none !important;
        margin-right: 0 !important;
      }

      .secondary-action-cell {
        text-align: center;
        width: 100%;
      }

      .header {
        margin-top: 20px !important;
        margin-bottom: 2px !important;
      }

      .shop-name__cell {
        display: block;
      }

      .order-number__cell {
        display: block;
        text-align: left !important;
        margin-top: 20px;
      }

      .po-number__cell {
        display: block;
        text-align: left !important;
        margin-top: 5px;
      }

      .button {
        width: 100%;
      }

      .or {
        margin-right: 0 !important;
      }

      .apple-wallet-button {
        text-align: center;
      }

      .customer-info__item {
        display: block;
        width: 100% !important;
      }

      .spacer {
        display: none;
      }

      .subtotal-spacer {
        display: none;
      }
    }
  </style>
</head>

<body style="margin: 0;">




  <title>Solicitud de editor</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width">





  <table class="body" style="height: 100% !important; width: 100% !important; border-spacing: 0; border-collapse: collapse;">
    <tbody>
      <tr>
        <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">
          <table class="header row" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin: 40px 0 20px;">
            <tbody>
              <tr>
                <td class="header__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">
                  <center>

                    <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                      <tbody>
                        <tr>
                          <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">

                            <table class="row" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                              <tbody>
                                <tr>
                                  <td class="shop-name__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">
                                    <img src="https://i.imgur.com/t74hsYr.png" alt="DanielOrozco" width="135">
                                  </td>

                                  <td class="order-number__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; text-transform: uppercase; font-size: 14px; color: #999;" align="right">
                                    <span class="order-number__text" style="font-size: 16px;">
                                    {{ $data['date'] }}
                                    </span>
                                  </td>
                                </tr>
                              </tbody>
                            </table>

                          </td>
                        </tr>
                      </tbody>
                    </table>

                  </center>
                </td>
              </tr>
            </tbody>
          </table>

          <table class="row content" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
            <tbody>
              <tr>
                <td class="content__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; padding-bottom: 40px; border-width: 0;">
                  <center>
                    <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                      <tbody>
                        <tr>
                          <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">

                            <h2 style="font-weight: normal; font-size: 24px; margin: 0 0 10px;">Hi! {{ $data['customerName'] }}</h2>
                            <p style="font-weight: normal; font-size: 18px; margin: 15px 0 10px;">Your access request has been approved. You can now access to get more information. Click the button below to get started: </p>

                            <table class="row actions" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
                              <tbody>
                                <tr>
                                  <td class="empty-line" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; line-height: 0em;">&nbsp;</td>
                                </tr>
                                <tr>
                                  <td class="actions__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;">
                                    <table class="button main-action-cell" style="border-spacing: 0; border-collapse: collapse; float: left; margin-right: 15px;">
                                      <tbody>
                                        <tr>
                                          <td class="button__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; border-radius: 4px;" align="center" bgcolor="#101010"><a href="https://danielorozcoestudio.com/account" class="button__text" style="font-size: 16px; text-decoration: none; display: block; color: #fff; padding: 20px 25px;">Access my account</a></td>
                                        </tr>
                                      </tbody>
                                    </table>

                                    {{-- <table class="link secondary-action-cell" style="border-spacing: 0; border-collapse: collapse; margin-top: 19px;">
                                      <tbody>
                                        <tr>
                                          <td class="link__cell" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; border-radius: 4px;" align="center">o <a href="https://www.google.com.ar?status=rechazado" style="font-size: 16px; text-decoration: none; color: #101010;">Rechazar esta solicitud</a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table> --}}
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </center>
                </td>
              </tr>
            </tbody>
          </table>





          <table class="row subtotal-table" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
            <tbody>
              <tr>
                <td colspan="2" class="subtotal-table__line" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; border-bottom-width: 1px; border-bottom-color: #e5e5e5; border-bottom-style: solid; height: 1px; padding: 0;"></td>
              </tr>
              <tr>
                <td colspan="2" class="subtotal-table__small-space" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; height: 10px;"></td>
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
  </center>
  </td>
  </tr>
  </tbody>
  </table>

  <img src="https://cdn.shopify.com/shopifycloud/shopify/assets/themes_support/notifications/spacer-1a26dfd5c56b21ac888f9f1610ef81191b571603cb207c6c0f564148473cab3c.png" class="spacer" height="1" style="min-width: 600px; height: 0;">

  </td>
  </tr>
  </tbody>
  </table>
</body>

</html>
