<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Announcement</title>
</head>
<body style="background-color: #f9fafb; font-family: sans-serif; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h2 style="font-size: 24px; font-weight: 600; color: #111827; margin-bottom: 12px;">
            {{ $announcement->title }}
        </h2>

        <p style="font-size: 16px; color: #374151; line-height: 1.5;">
            {{ $announcement->description }}
        </p>

        <div style="margin-top: 24px;">
            <a href="{{ url('/announcements/' . $announcement->id) }}"
               style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 10px 20px; font-weight: 600; border-radius: 6px; text-decoration: none;">
                View Announcement
            </a>
        </div>

        <p style="font-size: 14px; color: #9ca3af; margin-top: 40px;">
            Thanks,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
