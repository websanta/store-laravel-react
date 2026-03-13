<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h1 style="color: #2c3e50; margin-top: 0;">
            @if($status === 'approved')
                🎉 Congratulations!
            @elseif($status === 'rejected')
                ℹ️ Vendor Application Update
            @else
                Vendor Status Update
            @endif
        </h1>

        <p>Hello <strong>{{ $vendor->user->name }}</strong>,</p>

        @if($status === 'approved')
            <p>We are pleased to inform you that your vendor application has been <strong style="color: #28a745;">approved</strong>!</p>
            <p>You can now start selling your products on our platform.</p>

            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Store Information:</strong></p>
                <p style="margin: 5px 0;">Store Name: {{ $vendor->store_name }}</p>
                @if($vendor->store_address)
                    <p style="margin: 5px 0;">Store Address: {{ $vendor->store_address }}</p>
                @endif
            </div>

        @elseif($status === 'rejected')
            <p>We regret to inform you that your vendor application has been <strong style="color: #dc3545;">rejected</strong>.</p>

            @if($rejectionReason)
                <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Reason for rejection:</strong></p>
                    <p style="margin: 5px 0;">{{ $rejectionReason }}</p>
                </div>
            @endif

            <p>If you believe this is a mistake or would like to reapply, please contact our support team.</p>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <p style="margin: 0;">Best regards,</p>
            <p style="margin: 5px 0;"><strong>The Team</strong></p>
        </div>
    </div>
</body>
</html>
