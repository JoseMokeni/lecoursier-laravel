# Le Coursier Laravel App

## Pre-requisites

-   Docker Infrastructure running on your machine (see PFE-SERVICES repository)
-   **Firebase Project** set up with Firebase Cloud Messaging (FCM) enabled
-   **Stripe Account** created with:
    -   A product configured
    -   Two recurring prices (monthly and yearly subscriptions)
-   **Sentry Account** set up for error tracking and monitoring

## Installation

1. Clone the repository
2. Copy the `.env.example` file to `.env` and fill in the necessary environment variables
3. Run `docker compose up -d` to start the containers
4. Change the permissions of `.env` file to be writable by the web server:
    - For Linux: `sudo chmod 664 .env`
    - For Windows: Right-click on the `.env` file, go to Properties, and set the permissions accordingly.
    - For MacOS: `chmod 664 .env`
5. Run `docker compose exec app php artisan key:generate` to generate the application key
6. Run `docker compose exec app php artisan migrate` to run the migrations

## Firebase Configuration

To enable Firebase Cloud Messaging (FCM) and other Firebase services, you need to configure both the service account file and environment variables:

### 1. Configure Firebase Project ID

Update your `.env` file with your Firebase project ID:

```bash
FIREBASE_PROJECT_ID=your-firebase-project-id
```

You can find your project ID in the Firebase Console under Project Settings > General tab.

### 2. Setup Service Account File

1. **Download your Firebase service account key** from the Firebase Console:

    - Go to your Firebase project settings
    - Navigate to the "Service accounts" tab
    - Click "Generate new private key" and download the JSON file

2. **Place the service account file** in the following directory:

    ```
    storage/app/json/service-account.json
    ```

3. **Ensure the directory exists**:

    ```bash
    mkdir -p storage/app/json
    ```

4. **Set proper permissions** for the service account file:
    - For Linux/MacOS: `chmod 644 storage/app/json/service-account.json`
    - For Windows: Ensure the file is readable by the web server

⚠️ **Important**: Never commit this file to version control as it contains sensitive credentials. The `storage/app/json/` directory should be added to your `.gitignore` file.

## Stripe Configuration

Configure Stripe for payment processing by setting up the following environment variables in your `.env` file:

### 1. Stripe API Keys

```bash
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### 2. Product and Price Configuration

Set up your product and recurring prices:

```bash
STRIPE_PRODUCT_ID=prod_your_product_id
STRIPE_MONTHLY_PRICE_ID=price_your_monthly_price_id
STRIPE_YEARLY_PRICE_ID=price_your_yearly_price_id
```

### How to get these values:

1. **API Keys**: Found in your Stripe Dashboard under Developers > API keys
2. **Product ID**: Create a product in Stripe Dashboard under Products, then copy the product ID
3. **Price IDs**: Create recurring prices for your product (monthly and yearly), then copy each price ID
4. **Webhook Secret**:
    - **For Production**: Use Laravel Cashier to create webhooks (see below)
    - **For Local Development**: Use Stripe CLI (see below)

### Production Webhook Setup with Laravel Cashier

For production, use Laravel Cashier's built-in command to create the necessary webhooks:

1. **Create webhooks automatically**:

    ```bash
    docker compose exec app php artisan cashier:webhook
    ```

2. **Retrieve the webhook signing secret** from your Stripe Dashboard under Developers > Webhooks

3. **Update your `.env` file** with the webhook secret:
    ```bash
    STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_from_dashboard
    ```

### Local Development with Stripe CLI

For local development, use the Stripe CLI to forward webhooks to your local application:

1. **Install Stripe CLI**: Follow the [official installation guide](https://stripe.com/docs/stripe-cli)

2. **Login to your Stripe account**:

    ```bash
    stripe login
    ```

3. **Forward webhooks to your local app**:

    ```bash
    stripe listen --forward-to localhost:8000/stripe/webhook
    ```

4. **Copy the webhook signing secret** from the CLI output and update your `.env`:
    ```bash
    STRIPE_WEBHOOK_SECRET=whsec_your_local_webhook_secret_from_cli
    ```

The Stripe CLI will automatically forward all webhook events to your local application, making development and testing much easier.

## Sentry Configuration

Configure Sentry for error tracking and performance monitoring:

### Environment Variables

Update your `.env` file with your Sentry configuration:

```bash
SENTRY_LARAVEL_DSN=https://your_sentry_dsn@sentry.io/your_project_id
SENTRY_TRACES_SAMPLE_RATE=1.0
```

### How to get these values:

1. **Sentry DSN**: Found in your Sentry project settings under Client Keys (DSN)
2. **Traces Sample Rate**: Set to `1.0` for development (captures 100% of transactions) or a lower value like `0.1` for production

⚠️ **Note**: For production environments, consider reducing the `SENTRY_TRACES_SAMPLE_RATE` to avoid excessive data usage.
