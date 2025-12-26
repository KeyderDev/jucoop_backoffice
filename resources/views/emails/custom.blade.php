<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="x-apple-disable-message-reformatting" />
  <meta name="color-scheme" content="light only">
  <meta name="supported-color-schemes" content="light">
  <!--[if mso]>
  <xml>
    <o:OfficeDocumentSettings>
      <o:PixelsPerInch>96</o:PixelsPerInch>
      <o:AllowPNG/>
    </o:OfficeDocumentSettings>
  </xml>
  <![endif]-->
  <style>
    body { margin:0; padding:0; -webkit-text-size-adjust:100%; -ms-interpolation-mode:bicubic; background:#0b1620; }
    table { border-spacing:0; border-collapse:collapse; }
    img { border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; }
    a { text-decoration:none; }
    .preheader { display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; overflow:hidden; mso-hide:all; }
    @media (max-width: 640px) {
      .container { width:100% !important; }
      .px { padding-left:16px !important; padding-right:16px !important; }
      .cardPad { padding:18px 16px !important; }
      .stack { display:block !important; width:100% !important; }
      .centerMobile { text-align:center !important; }
      .logo { width:140px !important; }
    }
    @media (prefers-color-scheme: dark) {
      .noinvert { -webkit-filter:none !important; filter:none !important; }
    }
  </style>
</head>

<body style="margin:0; padding:0; background-color:#0b1620;">
  <div class="preheader">{{ $preheader ?? 'Mensaje de JuCoop' }}</div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0b1620" style="background-color:#0b1620;">
    <tr>
      <td align="center" style="padding:22px 12px;">

        <!--[if mso]><table role="presentation" width="620" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; width:100%; margin:0 auto;" class="container">

          <!-- HEADER -->
          <tr>
            <td align="center" style="padding:0 0 12px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:16px; overflow:hidden;">
                <tr>
                  <td bgcolor="#0f2027" style="background-color:#0f2027; padding:14px 18px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td align="left" class="stack centerMobile">
                          <img
                            src="{{ asset('images/coopceopng.png') }}"
                            alt="JuCoop"
                            width="155"
                            style="display:block; width:155px; height:auto;"
                            class="logo noinvert"
                          />
                        </td>
                        <td align="right" class="stack centerMobile" style="font-family:Arial, Helvetica, sans-serif;">
                          <div style="font-size:12px; line-height:1.2;">
                            <span style="display:inline-block; padding:6px 10px; border:1px solid rgba(151,213,105,.35); border-radius:999px; color:#97d569;">
                              JUCOOP • COOPCEO
                            </span>
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f2027" style="background-color:#0f2027; border-radius:18px; overflow:hidden;">
                <tr>
                  <td style="padding:22px 22px; font-family:Arial, Helvetica, sans-serif;" class="cardPad px">

                    <div style="font-size:14px; line-height:1.75; color:#cfe1f2;">
                      {!! nl2br(e($bodyText)) !!}
                    </div>

                    <div style="margin-top:18px; border-top:1px solid rgba(207,225,242,.14);"></div>

                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:16px;">
                      <tr>
                        <td class="stack" style="font-family:Arial, Helvetica, sans-serif;">
                          <div style="font-size:12px; color:#b9c8d6; line-height:1.6;">
                            <strong style="color:#ffffff;">Soporte:</strong>
                            <span style="color:#97d569;">{{ $supportEmail ?? 'coopceo0@gmail.com' }}</span>
                            <br>
                            <strong style="color:#ffffff;">Horario:</strong>
                            {{ $supportHours ?? 'Lun–Vie, 7:30am–4:00pm' }}
                          </div>
                        </td>
                      </tr>
                    </table>

                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="padding:14px 4px 0 4px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:1.6; color:#9db2c7;">
                    <span style="color:#cfe1f2;">© {{ date('Y') }} JuCoop / COOPCEO</span>
                    <span style="padding:0 8px; color:#3b556a;">•</span>
                    <a href="{{ $portalUrl ?? '#' }}" style="color:#97d569;">Portal</a>
                    <div style="margin-top:8px; color:#6f889c;">
                      Este correo fue enviado automáticamente. Si no solicitaste esta acción, puedes ignorarlo.
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
        <!--[if mso]></td></tr></table><![endif]-->

      </td>
    </tr>
  </table>
</body>
</html>
