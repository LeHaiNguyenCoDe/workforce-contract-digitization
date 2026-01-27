/**
 * Formats a number as a currency string with Vietnamese Dong symbol.
 * @param price - The numeric price to format.
 * @returns A string like "30 ₫".
 */
export const formatPrice = (price: number): string => {
  return price.toLocaleString() + ' ₫';
};

/**
 * Alternative currency formatter if needed for different locales in the future.
 */
export const formatCurrency = (val: number): string => {
  return val.toString() + ' ₫';
};
