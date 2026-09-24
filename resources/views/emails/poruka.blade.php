<!DOCTYPE html>
<html lang="sr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $naslov }}</title></head>
<body style="margin:0;padding:0;background:#f8f9ff;font-family:Inter,Segoe UI,Arial,sans-serif;color:#0b1c30">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9ff;padding:32px 12px">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <tr><td style="padding:24px 28px 0">
          <table role="presentation" cellpadding="0" cellspacing="0"><tr>
            <td style="width:32px;height:32px;background:#000;color:#fff;border-radius:4px;text-align:center;font-weight:700;font-size:16px">T</td>
            <td style="padding-left:10px;font-weight:600;font-size:14px">Temelj Investitor</td>
          </tr></table>
        </td></tr>
        <tr><td style="padding:24px 28px 8px">
          <h1 style="margin:0 0 12px;font-size:20px;line-height:1.3">{{ $naslov }}</h1>
          <p style="margin:0;font-size:14px;line-height:1.6;color:#45464d">{{ $tekst }}</p>
        </td></tr>
        @if(!empty($dugme) && !empty($link))
        <tr><td style="padding:16px 28px 8px">
          <a href="{{ $link }}" style="display:inline-block;background:#000;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:11px 20px;border-radius:4px">{{ $dugme }}</a>
          <p style="margin:14px 0 0;font-size:12px;color:#76777d;word-break:break-all">Ako dugme ne radi, otvorite: {{ $link }}</p>
        </td></tr>
        @endif
        <tr><td style="padding:20px 28px 24px;font-size:12px;color:#76777d">Temelj Investitor · deo platforme Temelj.rs</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
