# Dpd Classic

- Home delivery with DPD
- Export / import orders

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is DpdClassic.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file:

```
composer require thelia/dpd-classic-module:~2.0.0
```

## Usage

Once activated, you have to configure the module.

In the "*Configure sender address*", set the address from where orders will be sent (mainly your enterprise address).

In the "*Price slices*" tab, configure shipping fees depending on the cart's weight. You can also activate free shipping here.

Don't forget to assign the module to your shipping zones.

When customers order with this delivery method and when their orders are paid, orders appear in the "*Export*" tab. Here you can export them and select a new status for them.

Once you have exported orders and set them a delivery reference, you can import them in the "*Import*" tab.

When consulting an order done with DPD Classic, you can export it from the module tab of the order details.

## Hook

Apart from module configuration, another hook is used to allow you to export one order. You can find it in the order detail page, in the "*Modules*" tab.

## Loop

[dpdclassic.delivery]

Can be used to replace the "*delivery*" to check if the delivery module is DPD Classic for a specific integration.

Extends the *delivery* loop and has the same inputs and outputs, plus a **MODULE_ID** output representing DpdClassic module ID.

[dpdclassic.check.rights]

Used to check rights on the module.

### Output arguments

|Variable   |Description |
|---        |--- |
|$ERRMES    | Error message |
|$ERRFILE   | File on which user don't have rights |

### Example

{loop name="checkrights" type="dpdclassic.check.rights"}
    <div class="alert alert-danger">
        <p>{$ERRMES} {$ERRFILE} | {intl l="Please change the access rights"}.</p>
    </div>
{/loop}

{elseloop rel="checkrights"}
    Do something
{/elseloop}

[dpdclassic.orders]

Extends the *order* loop and has the same inputs and outputs, but only returns orders done with DpdClassic module and with *paid* or *processing* status.

[dpdclassic]

Returns slices of weight and corresponding prices of DpdClassic.

### Input arguments

|Variable   |Description |
|---        |--- |
|area       | ID of the area from which you want prices |

### Output arguments

|Variable   |Description |
|---        |--- |
|$MAX_WEIGHT| Limit of the weight slice |
|$PRICE     | Price of the weight slice |

[dpdclassic.urltracking]

Used for order tracking

### Input arguments

|Variable   |Description |
|---        |--- |
|ref        | Reference of the order you want to track |

### Output arguments

|Variable   |Description |
|---        |--- |
|$URL       | URL for order tracking |

### Example

{loop name="tracking" type="dpdclassic.urltracking" ref=$REF}
    {intl l="Track parcel"} <a href="{$URL}">{intl l="here"}</a>
{/loop}