/* eslint-disable no-undef */
const DIM_PRESET = {
  d: [40, 40, 5, 0.2],
  s: [40, 20, 10, 1],
  ss: [30, 30, 30, 3],
  m: [50, 40, 30, 6],
  l: [50, 50, 40, 12],
  h: [60, 50, 40, 20],
}

const DIMENSION_FIELDS = [
  '#_shipping_length',
  '#_shipping_width',
  '#_shipping_height',
  '#_shipping_weight',
]

jQuery(window).load(() => {
  const minDate = new Date()
  jQuery('#_shipping_pickup_date').datepicker('option', {
    minDate,
    maxDate: '+1w',
  })
  jQuery('#_shipping_pickup_time').timepicker({
    scrollDefault: 'now',
    timeFormat: 'H:i',
    step: PICKUPP_CREATE_ORDER_TIME_STEP,
    minTime: PICKUPP_SERVICE_START_TIME,
    maxTime: PICKUPP_SERVICE_END_TIME,
    forceRoundTime: true,
  })
  const date = jQuery('#_shipping_pickup_date').val()
  if (!date) {
    jQuery('#_shipping_pickup_date').datepicker('setDate', minDate)
  }
  const time = jQuery('#_shipping_pickup_time').val()
  if (!time) {
    const minTime = new Date()
    minTime.setMinutes((minTime.getMinutes() + PICKUPP_CREATE_ORDER_TIME_STEP))
    jQuery('#_shipping_pickup_time').timepicker('setTime', minTime)
  }
  const codAmount = jQuery('#_shipping_cod_amount').val()
  if (!codAmount) {
    jQuery('#_shipping_cod_amount').val(0)
  }
  jQuery('#_shipping_cod').change((evt) => {
    const { value } = evt.target
    if (value === '0') {
      jQuery('#_shipping_cod_amount').val(0)
      jQuery('#_shipping_cod_amount').prop('disabled', true)
    } else {
      jQuery('#_shipping_cod_amount').prop('disabled', false)
    }
  })

  jQuery('#_shipping_delivery_window option[value="standard"]').attr('disabled', true)
  jQuery('#_shipping_delivery_window option[value="express"]').attr('disabled', true)
  const deliveryWindow = jQuery('#_shipping_delivery_window').val()
  if (!deliveryWindow) {
    jQuery('#_shipping_delivery_window').val('4.0').change()
  }

  const onChangeDimension = (e) => {
    const preset = DIM_PRESET[e.target.value]
    jQuery.each(DIMENSION_FIELDS, (idx, fieldId) => {
      jQuery(fieldId).val(preset[idx]).change()
    })
  }

  // update on dimension presets
  jQuery('#_shipping_dimension_preset').on('change', onChangeDimension)

  // init default dimension upon create order
  if (DIMENSION_FIELDS.every(f => jQuery(f).val() === '')) {
    jQuery('#_shipping_dimension_preset').change()
  }
})
