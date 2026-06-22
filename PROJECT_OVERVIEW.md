# Tpay PrestaShop Module Overview

This document provides a high-level overview of the Tpay payment module for PrestaShop, intended to help developers quickly understand its architecture and key components.

## 1. Project Summary

- **Module Name**: `tpay`
- **Author**: Krajowy Integrator Płatności S.A. (Tpay)
- **Version**: 1.14.6 (as of analysis)
- **Compatibility**: PrestaShop 1.7+
- **Purpose**: Integrates the Tpay payment gateway with PrestaShop, allowing merchants to accept various online payment methods.

## 2. Core Architecture & Key Directories

The module follows a modern, structured approach, separating concerns into different directories within `src/`.

-   **`tpay.php`**: The main module entry point. It handles installation, uninstallation, configuration, and registers hooks.
-   **`controllers/`**: Contains front and admin controllers.
    -   `controllers/front/payment.php`: Initiates the payment process when a user selects a Tpay method.
    -   `controllers/front/notifications.php`: Handles incoming notifications from the Tpay server to update order statuses.
-   **`src/`**: The heart of the module's logic.
    -   **`Hook/`**: Contains classes that respond to PrestaShop's hooks (e.g., displaying payment options). `HookDispatcher.php` routes hooks to the correct class.
    -   **`Service/`**: Contains business logic services.
        -   `Service/PaymentOptions/PaymentOptionsService.php`: Crucial service that fetches available Tpay channels, groups them, and builds the payment options displayed at checkout.
    -   **`Factory/`**: Responsible for creating objects.
        -   `PaymentFactory.php`: Creates the correct payment *handler* (e.g., `BasicPaymentHandler`, `CreditCardPaymentHandler`) based on the selected payment type.
        -   `PaymentOptionsFactory.php`: Creates the correct payment *option* class (e.g., `Transfer`, `Blik`) for rendering on the checkout page.
    -   **`Handler/`**: Contains the logic for handling specific payment types.
        -   `PaymentHandler.php`: A generic handler that prepares customer and order data.
        -   `BasicPaymentHandler.php`, `CreditCardPaymentHandler.php`, etc.: Specific handlers that build the final API request for a given payment type and manage the response.
    -   **`Repository/`**: Manages database interactions, primarily for the custom `tpay_transaction` table.
-   **`views/`**: Contains Smarty templates (`.tpl`) for rendering front-end elements like payment options and confirmation pages.

## 3. Payment Processing Flow

Here is a step-by-step breakdown of how a standard bank transfer payment is processed:

1.  **Checkout Page (`paymentOptions` hook)**:
    -   `tpay.php` receives the `paymentOptions` hook.
    -   It delegates to `HookDispatcher`, which calls the `paymentOptions` method in `Hook/Payment.php`.
    -   `Hook/Payment.php` uses `Service/PaymentOptions/PaymentOptionsService.php` to get a list of available payment options.
    -   `PaymentOptionsService` calls the Tpay API to get available channels, filters them based on configuration, and groups them.
    -   It uses `Factory/PaymentOptionsFactory` to create objects like `Service/PaymentOptions/Transfer` for each payment type.
    -   These objects generate `PaymentOption` instances that PrestaShop displays to the user.

2.  **User Selects Payment & Confirms Order**:
    -   The user selects a Tpay method and clicks "Order with an obligation to pay".
    -   The form submits to `TpayPaymentModuleFrontController` (`controllers/front/payment.php`).

3.  **Payment Initiation (`TpayPaymentModuleFrontController`)**:
    -   The controller validates the cart and customer.
    -   It creates a PrestaShop order with a "Pending" status (`TPAY_PENDING`).
    -   It uses `Factory/PaymentFactory` to get the appropriate handler for the selected payment type (e.g., `BasicPaymentHandler` for a transfer).
    -   It instantiates `Handler/PaymentHandler`, passing it the specific payment handler object and order/customer data.

4.  **Transaction Creation (`Handler/PaymentHandler`)**:
    -   `PaymentHandler` gathers all required customer and order details using `CustomerData`.
    -   It calls the `createPayment` method on the specific handler (e.g., `BasicPaymentHandler`).

5.  **API Call & Redirect (`BasicPaymentHandler`)**:
    -   `BasicPaymentHandler` builds the final payload for the Tpay API.
    -   It calls the `createTransaction` method of the Tpay API library.
    -   Upon a successful response from Tpay, it receives a `transactionId` and a `transactionPaymentUrl`.
    -   It uses `Service/TransactionService` to save the transaction details to the database and then redirects the user's browser to the `transactionPaymentUrl`.

6.  **Payment Confirmation (Notifications)**:
    -   After the user completes the payment, Tpay sends a notification to the module's notification endpoint (`controllers/front/notifications.php`).
    -   The `NotificationsController` validates the notification and updates the corresponding PrestaShop order status (e.g., to "Payment accepted").
