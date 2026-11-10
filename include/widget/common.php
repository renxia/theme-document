<?php
/*
 * 表单输出
 *
 * 后台小工具表单的值统一用 esc_attr/esc_html 转义，避免保存的字段值破坏属性或注入脚本。
 * */

/**
 * 取表单字段的标量值（数组时按 index 取元素，非标量一律转空串）
 */
function widget_field_value( $value, $isArray, $args ) {
	if ( $isArray && is_array( $value ) ) {
		$value = isset( $value[ $args['index'] ] ) ? $value[ $args['index'] ] : '';
	}
	return is_scalar( $value ) ? $value : '';
}

function widget_input( $that, $instance, $args, $isArray = false ) {
	$value = widget_field_value( $that->getValue( $instance, $args['field'], $args['default'] ), $isArray, $args );
	?>
    <p>
        <label for="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>">
    <div style="margin-bottom: 0.5rem;"><?php echo esc_html( $args['title'] ); ?></div>
    <input class="widefat" id="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>"
           name="<?php echo esc_attr( $that->get_field_name( $args['field'] ) . ( $isArray ? '[]' : '' ) ); ?>"
           type="<?php echo esc_attr( $args['type'] ); ?>"
           value="<?php echo esc_attr( $value ); ?>"/></label>
    </p>
	<?php
}


/*
 * 时间选择
 * */
function widget_datepicker( $that, $instance, $args, $isArray = false ) {
	static $count = 1;
	$value = widget_field_value( $that->getValue( $instance, $args['field'], $args['default'] ), $isArray, $args );
	?>
    <p>
        <label for="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>">
    <div style="margin-bottom: 0.5rem;"><?php echo esc_html( $args['title'] ); ?></div>
    <input id="select-datetime-<?php echo esc_attr( $count ); ?>" data-count="<?php echo esc_attr( $count ); ?>" class="widefat select-datetime"
           id="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>"
           name="<?php echo esc_attr( $that->get_field_name( $args['field'] ) . ( $isArray ? '[]' : '' ) ); ?>"
           type="<?php echo esc_attr( $args['type'] ); ?>"
           value="<?php echo esc_attr( $value ); ?>"/></label>
    </p>
	<?php
	$count ++;
}

/*
 * 时间选择
 * */
function widget_media( $that, $instance, $args, $isArray = false ) {
	$value = widget_field_value( $that->getValue( $instance, $args['field'], $args['default'] ), $isArray, $args );
	?>
    <p>
        <label for="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>">
    <div style="margin-bottom: 0.5rem;"><?php echo esc_html( $args['title'] ); ?></div>
    <div style="display: flex;align-items: center;">
        <input class="widefat" id="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>"
               name="<?php echo esc_attr( $that->get_field_name( $args['field'] ) . ( $isArray ? '[]' : '' ) ); ?>"
               type="<?php echo esc_attr( $args['type'] ); ?>"
               value="<?php echo esc_attr( $value ); ?>"/></label>
        <button style="margin-left: 10px;white-space: nowrap;" class="select-media" type="button">选择</button>
    </div>


    </p>
	<?php
}


/*
 * 选择框
 * 考虑到值是数组的情况
 * */
function widget_select( $that, $instance, $args, $options, $isArray = false ) {
	$current = widget_field_value( $that->getValue( $instance, $args['field'], $args['default'] ), $isArray, $args );
	?>

    <p>
        <label for="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>">
    <div style="margin-bottom: 0.5rem;"><?php echo esc_html( $args['title'] ); ?></div>
    <select class="widefat" id="<?php echo esc_attr( $that->get_field_id( $args['field'] ) ); ?>"
            name="<?php echo esc_attr( $that->get_field_name( $args['field'] ) . ( $isArray ? '[]' : '' ) ); ?>">
		<?php
		foreach ( $options as $key => $value ) {
			?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $current ); ?>>
				<?php echo esc_html( $value ); ?>
            </option>
		<?php } ?>
    </select>
    </label>
    </p>
	<?php
}
