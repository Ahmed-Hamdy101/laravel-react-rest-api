# AI Architecture

Create the AI integration as a provider-independent module in the Laravel application:

```text
app/Modules/AI/
    Application/
        Actions/
        DTOs/
        Services/
    Domain/
        Contracts/
        Policies/
    Infrastructure/
        Providers/
        Prompts/
        Persistence/
    Presentation/
        Controllers/
        Requests/
        Resources/
```

## Provider Contract

```php
interface AIProvider
{
    public function generate(AIRequest $request): AIResponse;
}
```

The application should depend on this contract rather than directly on OpenAI, Azure OpenAI, or another provider.

## Initial Features

### Customer support assistant

Answer authorized questions about order status, payment status, refund policy, and products.

### Product search and recommendations

Support natural-language product search, categorization, related products, and recommendations.

### Admin analytics assistant

Allow authorized administrators to ask about sales, order trends, product performance, and payment failures. The assistant should call approved reporting tools rather than receive unrestricted database access.

### Payment failure classification

Classify safe failure information such as provider timeout, authentication required, insufficient funds, or invalid customer input. AI must not approve payments or issue refunds.

## AI Data Model

Recommended tables:

```text
ai_conversations
ai_messages
ai_usage_records
ai_tool_calls
```

Validate every AI response in application code before using it in a business workflow.
