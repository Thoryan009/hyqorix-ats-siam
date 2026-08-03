import JsBarcode from 'jsbarcode'

export function generateBarcode(visaNo, el, options = {}) {
  if (!el) return
  JsBarcode(el, visaNo, {
    format: 'CODE128',
    width: 2,
    height: 40,
    displayValue: false,
    ...options,
  })
}
