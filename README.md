# pollVote

Playing around with Symfony, API-Platform, JS and Mercure Hub. 

PollVote is a simple test page that shows how one can use Mercure Hub to publish/update events across multiple clients

To Run Mercure:

JWT_KEY='PUT_YOUR_SECRET_HERE' ADDR=':3000' DEMO=1 ALLOW_ANONYMOUS=1 PUBLISH_ALLOWED_ORIGINS='http://localhost:8000' CORS_ALLOWED_ORIGINS='*' ./mercure 