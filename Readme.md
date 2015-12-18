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
composer require thelia/dpd-classic-module:~1.0
```

## Usage

Once activated, you have to configure the module.

In the "Configure sender address", set the address from where orders will be sent (mainly your enterprise address).

In the "Price slices" tab, configure shipping fees depending on the cart's weight. You can also activate free shipping here.

Don't forget to assign the module to your shipping zones.

When customers order with this delivery method and when their orders are paid, orders appear in the "Export" tab. Here you can export them and select a new status for them.

Once you have exported orders and set them a delivery reference, you can import them in the "Import" tab.

## Hook

If your module use one or more hook, fill this part. Explain which hooks are used.


## Loop

If your module declare one or more loop, describe them here like this :

[loop name]

### Input arguments

|Argument |Description |
|---      |--- |
|**arg1** | describe arg1 with an exemple. |
|**arg2** | describe arg2 with an exemple. |

### Output arguments

|Variable   |Description |
|---        |--- |
|$VAR1    | describe $VAR1 variable |
|$VAR2    | describe $VAR2 variable |

### Exemple

Add a complete exemple of your loop

## Other ?

If you have other think to put, feel free to complete your readme as you want.
