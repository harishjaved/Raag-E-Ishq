<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Tickets — Raag-E-Ishq</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #0c2238; color: #fff; padding: 20px; border-radius: 8px; text-align: center;">
        <h1 style="color: #e5a93b; margin: 0; font-size: 24px;">Raag-E-Ishq</h1>
        <p style="margin: 5px 0 0; font-size: 14px;">Joygivers Presents &bull; 17th January, 7:00 PM &bull; For Girl Education</p>
    </div>

    <div style="padding: 20px 0;">
        <p>Dear {{ $booking->contact->full_name }},</p>
        <p>Thank you for booking your admission to <strong>Raag-E-Ishq</strong> for girl education. Your payment has been verified and your tickets are ready!</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9; border-radius: 6px;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Booking Reference:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-family: monospace;">{{ $booking->booking_reference }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Venue:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">Chavara Hall, Kochi</td>
            </tr>
            <tr>
                <td style="padding: 10px;"><strong>Admissions:</strong></td>
                <td style="padding: 10px;">{{ $booking->tickets->count() }} Seat(s)</td>
            </tr>
        </table>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $passUrl ?? route('booking.confirmed', ['reference' => $booking->booking_reference]) }}" 
               style="background-color: #e5a93b; color: #000; font-weight: bold; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block;">
                View &amp; Download Admission Tickets
            </a>
        </div>

        <p style="font-size: 13px; color: #666;">
            <strong>Gate Instructions:</strong> Please present the unique QR code on each ticket when entering Chavara Hall. Each ticket is valid for one accepted admission.
        </p>
    </div>

    <div style="border-top: 1px solid #eee; padding-top: 20px; font-size: 12px; color: #888; text-align: center;">
        <p>Joygivers &bull; events@joygivers.org</p>
    </div>
</body>
</html>
