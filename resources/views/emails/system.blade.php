<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $mailData['title'] ?? 'Elimutaifa Labs' }}</title>
</head>
<body style="margin:0;padding:0;background:#f2f6fb;font-family:Arial,Helvetica,sans-serif;color:#15243a">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">{{ $mailData['preheader'] ?? ($mailData['intro'] ?? 'A message from Elimutaifa Labs') }}</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f2f6fb;padding:32px 12px">
        <tr><td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e1e9f2">
                <tr><td style="height:7px;background:linear-gradient(90deg,#0759d5,#09a8ed)"></td></tr>
                <tr><td style="padding:25px 32px 19px;border-bottom:1px solid #edf1f6">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>
                        <td style="vertical-align:middle;padding-right:12px"><img src="{{ url('images/elimutaifa-labs-mark.png') }}" width="48" height="48" alt="Elimutaifa Labs" style="display:block;border:0;width:48px;height:48px"></td>
                        <td style="vertical-align:middle"><div style="font-size:16px;font-weight:700;letter-spacing:.1px;color:#142944">Elimutaifa Labs</div><div style="font-size:11px;color:#6b7c93;margin-top:3px">Developer services</div></td>
                    </tr></table>
                </td></tr>
                <tr><td style="padding:32px">
                    @if (!empty($mailData['eyebrow']))<div style="font-size:11px;line-height:1.4;font-weight:700;letter-spacing:1.1px;text-transform:uppercase;color:#0875d1;margin-bottom:10px">{{ $mailData['eyebrow'] }}</div>@endif
                    <h1 style="margin:0 0 12px;font-size:25px;line-height:1.25;color:#142944;font-weight:700">{{ $mailData['title'] ?? 'A message from Elimutaifa Labs' }}</h1>
                    @if (!empty($mailData['intro']))<p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#53657c">{{ $mailData['intro'] }}</p>@endif
                    @if (!empty($mailData['code']))
                        <div style="margin:22px 0;padding:19px;text-align:center;border:1px solid #d8e9fb;border-radius:12px;background:#f5faff">
                            <div style="font-size:11px;color:#62758d;text-transform:uppercase;letter-spacing:1px">Your verification code</div>
                            <div style="margin-top:8px;font-size:34px;line-height:1.2;font-weight:750;letter-spacing:8px;color:#0759d5">{{ $mailData['code'] }}</div>
                        </div>
                    @endif
                    @if (!empty($mailData['details']))
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:18px 0;background:#f7f9fc;border:1px solid #e8edf4;border-radius:10px">
                            @foreach ($mailData['details'] as $label => $value)
                                <tr><td style="padding:11px 14px;border-bottom:1px solid #e8edf4;font-size:12px;color:#708198;width:36%;vertical-align:top">{{ $label }}</td><td style="padding:11px 14px;border-bottom:1px solid #e8edf4;font-size:13px;font-weight:600;color:#243a55;word-break:break-word">{{ $value }}</td></tr>
                            @endforeach
                        </table>
                    @endif
                    @if (!empty($mailData['button_url']) && !empty($mailData['button_label']))
                        <div style="margin:24px 0 6px"><a href="{{ $mailData['button_url'] }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background:#0759d5;color:#fff;text-decoration:none;font-size:14px;font-weight:700">{{ $mailData['button_label'] }}</a></div>
                    @endif
                    @if (!empty($mailData['note']))<p style="margin:18px 0 0;font-size:12px;line-height:1.65;color:#718198">{{ $mailData['note'] }}</p>@endif
                </td></tr>
                <tr><td style="padding:18px 32px;background:#f8fafc;border-top:1px solid #edf1f6;font-size:11px;line-height:1.6;color:#8190a3">Elimutaifa Labs · Secure developer hosting and database services<br>This is an automated message. Please do not reply with passwords or verification codes.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
