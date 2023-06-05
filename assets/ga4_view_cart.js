jQuery(document).ready(function () {
  dataLayer.push({ ecommerce: null });
  dataLayer.push({
    event: "view_item_list",
    ecommerce: {
      currency: item_list_data["currency"],
      item_list_id: item_list_data["item_list_id"],
      item_list_name: item_list_data["item_list_name"],
      items: item_list_data["items"],
    },
  });
});
