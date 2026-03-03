/**
 * Formats a number as currency using the Peso symbol (₱)
 * @param amount - The numeric amount to format
 * @returns Formatted string with Peso symbol (e.g., "₱1,234.56")
 */
export function formatCurrency(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    if (isNaN(num)) {
        return '₱0.00';
    }
    
    return `₱${num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
}

/**
 * Formats a number as currency without symbol
 * @param amount - The numeric amount to format
 * @returns Formatted string (e.g., "1,234.56")
 */
export function formatCurrencyAmount(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    if (isNaN(num)) {
        return '0.00';
    }
    
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
