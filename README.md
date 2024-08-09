# Custom Payment Methods Based on Category

Este plugin para WooCommerce altera os métodos de pagamento disponíveis com base nas categorias dos produtos no carrinho e adiciona um alerta para evitar combinações de pagamento incompatíveis.

## Descrição

O plugin **Custom Payment Methods Based on Category** permite ajustar os métodos de pagamento disponíveis no WooCommerce com base nas categorias dos produtos adicionados ao carrinho. Se um produto de uma categoria específica estiver no carrinho, o plugin ajusta quais métodos de pagamento estarão disponíveis para o cliente. Além disso, ele exibe um alerta se produtos de categorias incompatíveis forem encontrados no carrinho.

## Funcionalidades

- **Filtragem de Métodos de Pagamento**: O plugin exibe métodos de pagamento específicos com base nas categorias dos produtos no carrinho.
- **Alerta de Compatibilidade**: Se o carrinho contiver produtos de categorias incompatíveis, um alerta é exibido para o cliente.
- **Gerenciamento de Erros**: O alerta é exibido apenas uma vez por sessão de checkout para evitar mensagens repetidas.

## Instalação

1. Faça o download do arquivo do plugin.
2. Faça o upload do arquivo do plugin para o diretório `/wp-content/plugins/` no seu servidor.
3. No painel de administração do WordPress, vá para **Plugins** e ative o **Custom Payment Methods Based on Category**.

## Configuração

Não há configurações adicionais necessárias para este plugin. Após a ativação, ele começará a ajustar os métodos de pagamento disponíveis e exibir alertas conforme descrito.

## Código

O plugin inclui as seguintes funcionalidades:

- **`custom_payment_methods_based_on_category`**: Filtra os métodos de pagamento disponíveis com base nas categorias dos produtos no carrinho.
- **`get_term_ids_by_names`**: Obtém os IDs das categorias de produtos a partir dos seus nomes.
- **`has_asaas_category`**: Verifica se um produto pertence a uma das categorias específicas.
- **`is_descendant_of`**: Verifica se uma categoria é descendente de uma das categorias especificadas.
- **`reset_custom_payment_error_notice`**: Remove o alerta de erro quando o formulário de checkout é exibido.

## Contribuindo

Se você encontrar bugs ou tiver sugestões de melhorias, sinta-se à vontade para abrir uma issue ou enviar um pull request.

## Licença

Este plugin é distribuído sob a licença GPL-2.0 ou superior.

## Autor

**Mateus Cardoso**

Se precisar de suporte ou tiver dúvidas, você pode entrar em contato através do e-mail: mateusscardosoodev@gmail.com | +55 (41) 98774-2206.

---

**Nota:** Este plugin é desenvolvido para trabalhar com WooCommerce e pode não ser compatível com outras soluções de e-commerce.
