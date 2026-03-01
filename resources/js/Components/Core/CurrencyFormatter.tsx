import React from 'react';

export default function CurrencyFormatter(
  {
    amount,
    currency='USD',
    locale='en-US'
  }: {
    amount: number,
    currency?: string,
    locale?: string
  }) {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency
  }).format(amount);
}
