<?php
/**
 * Class WordPress\Plugin_Check\CLI\Plugin_Check_Command
 *
 * @package plugin-check
 */

namespace WordPress\Plugin_Check\CLI;

use Exception;
use WordPress\Plugin_Check\Checker\Check;
use WordPress\Plugin_Check\Checker\Check_Categories;
use WordPress\Plugin_Check\Checker\Check_Repository;
use WordPress\Plugin_Check\Checker\CLI_Runner;
use WordPress\Plugin_Check\Checker\Default_Check_Repository;
use WordPress\Plugin_Check\Plugin_Context;
use WordPress\Plugin_Check\Utilities\Plugin_Request_Utility;
use WP_CLI;

/**
 * Plugin check command.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class Plugin_Check_Command {

	/**
	 * Plugin context.
	 *
	 * @since 1.0.0
	 * @var Plugin_Context
	 */
	protected $plugin_context;

	/**
	 * Output format type.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	protected $output_formats = array(
		'table',
		'csv',
		'json',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin_Context $plugin_context Plugin context.
	 */
	public function __construct( Plugin_Context $plugin_context ) {
		$this->plugin_context = $plugin_context;
	}

	/**
	 * Runs plugin check.
	 *
	 * ## OPTIONS
	 *
	 * <plugin>
	 * : The plugin to check. Plugin name.
	 *
	 * [--checks=<checks>]
	 * : Only runs checks provided as an argument in comma-separated values, e.g. i18n_usage, late_escaping. Otherwise runs all checks.
	 *
	 * [--exclude-checks=<checks>]
	 * : Exclude checks provided as an argument in comma-separated values, e.g. i18n_usage, late_escaping.
	 * Applies after evaluating `--checks`.
	 *
	 * [--ignore-codes=<codes>]
	 * : Ignore error codes provided as an argument in comma-separated values.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * [--categories]
	 * : Limit displayed results to include only specific categories Checks.
	 *
	 * [--fields=<fields>]
	 * : Limit displayed results to a subset of fields provided.
	 *
	 * [--ignore-warnings]
	 * : Limit displayed results to exclude warnings.
	 *
	 * [--ignore-errors]
	 * : Limit displayed results to exclude errors.
	 *
	 * [--include-experimental]
	 * : Include experimental checks.
	 *
	 * [--exclude-directories=<directories>]
	 * : Additional directories to exclude from checks.
	 * By default, `.git`, `vendor` and `node_modules` directories are excluded.
	 *
	 * [--exclude-files=<files>]
	 * : Additional files to exclude from checks.
	 *
	 * [--severity=<severity>]
	 * : Severity level.
	 *
	 * [--error-severity=<error-severity>]
	 * : Error severity level.
	 *
	 * [--warning-severity=<warning-severity>]
	 * : Warning severity level.
	 *
	 * [--include-low-severity-errors]
	 * : Include errors with lower severity than the threshold as other type.
	 *
	 * [--include-low-severity-warnings]
	 * : Include warnings with lower severity than the threshold as other type.
	 *
	 * [--slug=<slug>]
	 * : Slug to override the default.
	 *
	 * ## EXAMPLES
	 *
	 *   wp plugin check akismet
	 *   wp plugin check akismet --checks=late_escaping
	 *   wp plugin check akismet --format=json
	 *
	 * @subcommand check
	 *
	 * @since 1.0.0
	 *
	 * @param array $args       List of the positional arguments.
	 * @param array $assoc_args List of the associative arguments.
	 *
	 * @throws Exception Throws exception.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function check( $args, $assoc_args ) {
		// Get options based on the CLI arguments.
		$options = $this->get_options(
			$assoc_args,
			array(
				'checks'                        => '',
				'format'                        => 'table',
				'ignore-warnings'               => false,
				'ignore-errors'                 => false,
				'include-experimental'          => false,
				'severity'                      => '',
				'error-severity'                => '',
				'warning-severity'              => '',
				'include-low-severity-errors'   => false,
				'include-low-severity-warnings' => false,
				'slug'                          => '',
				'ignore-codes'                  => '',
			)
		);

		// Create the plugin and checks array from CLI arguments.
		$plugin = isset( $args[0] ) ? $args[0] : '';
		$checks = wp_parse_list( $options['checks'] );

		// Ignore codes.
		$ignore_codes = isset( $options['ignore-codes'] ) ? wp_parse_list( $options['ignore-codes'] ) : array();

		// Create the categories array from CLI arguments.
		$categories = isset( $options['categories'] ) ? wp_parse_list( $options['categories'] ) : array();

		$excluded_directories = isset( $options['exclude-directories'] ) ? wp_parse_list( $options['exclude-directories'] ) : array();

		add_filter(
			'wp_plugin_check_ignore_directories',
			static function ( $dirs ) use ( $excluded_directories ) {
				return array_unique( array_merge( $dirs, $excluded_directories ) );
			}
		);

		$excluded_files = isset( $options['exclude-files'] ) ? wp_parse_list( $options['exclude-files'] ) : array();

		add_filter(
			'wp_plugin_check_ignore_files',
			static function ( $dirs ) use ( $excluded_files ) {
				return array_unique( array_merge( $dirs, $excluded_files ) );
			}
		);

		// Get the CLI Runner.
		$runner = Plugin_Request_Utility::get_runner();

		// Create the runner if not already initialized.
		if ( is_null( $runner ) ) {
			$runner = new CLI_Runner();
		}

		// Make sure we are using the correct runner instance.
		if ( ! ( $runner instanceof CLI_Runner ) ) {
			WP_CLI::error(
				__( 'CLI Runner was not initialized correctly.', 'plugin-check' )
			);
		}

		try {
			$runner->set_experimental_flag( $options['include-experimental'] );
			$runner->set_check_slugs( $checks );
			$runner->set_plugin( $plugin );
			$runner->set_categories( $categories );
			$runner->set_slug( $options['slug'] );
		} catch ( Exception $error ) {
			WP_CLI::error( $error->getMessage() );
		}

		$result = false;
		// Run checks against the plugin.
		try {
			$result = $runner->run();
		} catch ( Exception $error ) {
			Plugin_Request_Utility::destroy_runner();

			WP_CLI::error( $error->getMessage() );
		}

		Plugin_Request_Utility::destroy_runner();

		// Get errors and warnings from the results.
		$errors = array();
		if ( $result && empty( $assoc_args['ignore-errors'] ) ) {
			$errors = $result->get_errors();
		}
		$warnings = array();
		if ( $result && empty( $assoc_args['ignore-warnings'] ) ) {
			$warnings = $result->get_warnings();
		}

		if ( empty( $errors ) && empty( $warnings ) ) {
			WP_CLI::success( __( 'Checks complete. No errors found.', 'plugin-check' ) );

			return;
		}

		// Default fields.
		$default_fields = $this->get_check_default_fields( $assoc_args );

		// Get formatter.
		$formatter = $this->get_formatter( $assoc_args, $default_fields );

		// Severity.
		$error_severity                = ! empty( $options['error-severity'] ) ? $options['error-severity'] : $options['severity'];
		$warning_severity              = ! empty( $options['warning-severity'] ) ? $options['warning-severity'] : $options['severity'];
		$include_low_severity_errors   = ! empty( $options['include-low-severity-errors'] ) ? true : false;
		$include_low_severity_warnings = ! empty( $options['include-low-severity-warnings'] ) ? true : false;

		// Print the formatted results.
		// Go over all files with errors first and print them, combined with any warnings in the same file.
		foreach ( $errors as $file_name => $file_errors ) {
			$file_warnings = array();
			if ( isset( $warnings[ $file_name ] ) ) {
				$file_warnings = $warnings[ $file_name ];
				unset( $warnings[ $file_name ] );
			}
			$file_results = $this->flatten_file_results( $file_errors, $file_warnings );

			if ( ! empty( $ignore_codes ) ) {
				$file_results = $this->get_filtered_results_by_ignore_codes( $file_results, $ignore_codes );
			}

			if ( '' !== $error_severity || '' !== $warning_severity ) {
				$file_results = $this->get_filtered_results_by_severity( $file_results, intval( $error_severity ), intval( $warning_severity ), $include_low_severity_errors, $include_low_severity_warnings );
			}

			if ( ! empty( $file_results ) ) {
				$this->display_results( $formatter, $file_name, $file_results );
			}
		}

		// If there are any files left with only warnings, print those next.
		foreach ( $warnings as $file_name => $file_warnings ) {
			$file_results = $this->flatten_file_results( array(), $file_warnings );

			if ( ! empty( $ignore_codes ) ) {
				$file_results = $this->get_filtered_results_by_ignore_codes( $file_results, $ignore_codes );
			}

			if ( '' !== $error_severity || '' !== $warning_severity ) {
				$file_results = $this->get_filtered_results_by_severity( $file_results, intval( $error_severity ), intval( $warning_severity ), $include_low_severity_errors, $include_low_severity_warnings );
			}

			if ( ! empty( $file_results ) ) {
				$this->display_results( $formatter, $file_name, $file_results );
			}
		}
	}

	/**
	 * Lists the available checks for plugins.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit displayed results to a subset of fields provided.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * [--categories]
	 * : Limit displayed results to include only specific categories.
	 *
	 * [--include-experimental]
	 * : Include experimental checks.
	 *
	 * ## EXAMPLES
	 *
	 *   wp plugin list-checks
	 *   wp plugin list-checks --format=json
	 *
	 * @subcommand list-checks
	 *
	 * @since 1.0.0
	 *
	 * @param array $args       List of the positional arguments.
	 * @param array $assoc_args List of the associative arguments.
	 *
	 * @throws WP_CLI\ExitException Show error if invalid format argument.
	 */
	public function list_checks( $args, $assoc_args ) {
		$check_repo = new Default_Check_Repository();

		// Get options based on the CLI arguments.
		$options = $this->get_options(
			$assoc_args,
			array(
				'format'               => 'table',
				'categories'           => '',
				'include-experimental' => false,
			)
		);

		$check_flags = Check_Repository::TYPE_ALL;

		// Check whether to include experimental checks.
		if ( $options['include-experimental'] ) {
			$check_flags = $check_flags | Check_Repository::INCLUDE_EXPERIMENTAL;
		}

		$collection = $check_repo->get_checks( $check_flags );

		// Filters the checks by specific categories.
		if ( ! empty( $options['categories'] ) ) {
			$categories = array_map( 'trim', explode( ',', $options['categories'] ) );
			$collection = Check_Categories::filter_checks_by_categories( $collection, $categories );
		}

		$all_checks = array();

		/**
		 * All checks to list.
		 *
		 * @var Check $check
		 */
		foreach ( $collection as $key => $check ) {
			$item = array();

			$item['slug']        = $key;
			$item['stability']   = strtolower( $check->get_stability() );
			$item['category']    = join( ', ', $check->get_categories() );
			$item['description'] = $check->get_description();
			$item['url']         = $check->get_documentation_url();

			$all_checks[] = $item;
		}

		// Get formatter.
		$formatter = $this->get_formatter(
			$options,
			array(
				'slug',
				'category',
				'stability',
				'description',
				'url',
			)
		);

		// Display results.
		$formatter->display_items( $all_checks );
	}

	/**
	 * Lists the available check categories for plugins.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit displayed results to a subset of fields provided.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *   wp plugin list-check-categories
	 *   wp plugin list-check-categories --format=json
	 *
	 * @subcommand list-check-categories
	 *
	 * @since 1.0.0
	 *
	 * @param array $args       List of the positional arguments.
	 * @param array $assoc_args List of the associative arguments.
	 *
	 * @throws WP_CLI\ExitException Show error if invalid format argument.
	 */
	public function list_check_categories( $args, $assoc_args ) {
		// Get options based on the CLI arguments.
		$options = $this->get_options( $assoc_args, array( 'format' => 'table' ) );

		// Get check categories details.
		$categories = $this->get_check_categories();

		// Get formatter.
		$formatter = $this->get_formatter(
			$options,
			array(
				'name',
				'slug',
			)
		);

		// Display results.
		$formatter->display_items( $categories );
	}

	/**
	 * Returns check categories details.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of the check categories.
	 */
	private function get_check_categories() {
		$check_categories = new Check_Categories();
		$all_categories   = $check_categories->get_categories();

		$categories = array();

		foreach ( $all_categories as $slug => $label ) {
			$categories[] = array(
				'slug' => $slug,
				'name' => $label,
			);
		}

		return $categories;
	}

	/**
	 * Validates the associative arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $assoc_args List of the associative arguments.
	 * @param array $defaults   List of the default arguments.
	 * @return array List of the associative arguments.
	 *
	 * @throws WP_CLI\ExitException Show error if invalid format argument.
	 */
	private function get_options( $assoc_args, $defaults ) {
		$options = wp_parse_args( $assoc_args, $defaults );

		if ( ! in_array( $options['format'], $this->output_formats, true ) ) {
			WP_CLI::error(
				sprintf(
					// translators: 1. Output formats.
					__( 'Invalid format argument, valid value will be one of [%1$s]', 'plugin-check' ),
					implode( ', ', $this->output_formats )
				)
			);
		}

		return $options;
	}

	/**
	 * Gets the formatter instance to format check results.
	 *
	 * @since 1.0.0
	 *
	 * @param array $assoc_args     Associative arguments.
	 * @param array $default_fields Default fields.
	 * @return WP_CLI\Formatter The formatter instance.
	 */
	private function get_formatter( $assoc_args, $default_fields ) {
		if ( isset( $assoc_args['fields'] ) ) {
			$default_fields = wp_parse_args( $assoc_args['fields'], $default_fields );
		}

		return new WP_CLI\Formatter(
			$assoc_args,
			$default_fields
		);
	}

	/**
	 * Returns check default fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $assoc_args Associative arguments.
	 * @return array Default fields.
	 */
	private function get_check_default_fields( $assoc_args ) {
		$default_fields = array(
			'line',
			'column',
			'code',
			'message',
			'docs',
		);

		// If both errors and warnings are included, display the type of each result too.
		if ( empty( $assoc_args['ignore_errors'] ) && empty( $assoc_args['ignore_warnings'] ) ) {
			$default_fields = array(
				'line',
				'column',
				'type',
				'code',
				'message',
				'docs',
			);
		}

		return $default_fields;
	}

	/**
	 * Flattens and combines the given associative array of file errors and file warnings into a two-dimensional array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file_errors   Errors from a Check_Result, for a specific file.
	 * @param array $file_warnings Warnings from a Check_Result, for a specific file.
	 * @return array Combined file results.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	private function flatten_file_results( $file_errors, $file_warnings ) {
		$file_results = array();

		foreach ( $file_errors as $line => $line_errors ) {
			foreach ( $line_errors as $column => $column_errors ) {
				foreach ( $column_errors as $column_error ) {

					$column_error['message'] = str_replace( array( '<br>', '<strong>', '</strong>', '<code>', '</code>' ), array( ' ', '', '', '`', '`' ), $column_error['message'] );
					$column_error['message'] = html_entity_decode( $column_error['message'] );

					$file_results[] = array_merge(
						$column_error,
						array(
							'type'   => 'ERROR',
							'line'   => $line,
							'column' => $column,
						)
					);
				}
			}
		}

		foreach ( $file_warnings as $line => $line_warnings ) {
			foreach ( $line_warnings as $column => $column_warnings ) {
				foreach ( $column_warnings as $column_warning ) {

					$column_warning['message'] = str_replace( array( '<br>', '<strong>', '</strong>', '<code>', '</code>' ), array( ' ', '', '', '`', '`' ), $column_warning['message'] );

					$file_results[] = array_merge(
						$column_warning,
						array(
							'type'   => 'WARNING',
							'line'   => $line,
							'column' => $column,
						)
					);
				}
			}
		}

		usort(
			$file_results,
			static function ( $a, $b ) {
				if ( $a['line'] < $b['line'] ) {
					return -1;
				}
				if ( $a['line'] > $b['line'] ) {
					return 1;
				}
				if ( $a['column'] < $b['column'] ) {
					return -1;
				}
				if ( $a['column'] > $b['column'] ) {
					return 1;
				}
				return 0;
			}
		);

		return $file_results;
	}

	/**
	 * Displays the results.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_CLI\Formatter $formatter    Formatter class.
	 * @param string           $file_name    File name.
	 * @param array            $file_results Results.
	 */
	private function display_results( $formatter, $file_name, $file_results ) {
		WP_CLI::line(
			sprintf(
				'FILE: %s',
				$file_name
			)
		);

		$formatter->display_items( $file_results );

		WP_CLI::line();
		WP_CLI::line();
	}

	/**
	 * Returns check results filtered by severity level.
	 *
	 * @since 1.1.0
	 *
	 * @param array $results                       Check results.
	 * @param int   $error_severity                Error severity level.
	 * @param int   $warning_severity              Warning severity level.
	 * @param bool  $include_low_severity_errors   Include less level of severity issues as warning.
	 * @param bool  $include_low_severity_warnings Include less level of severity issues as warning.
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 * @return array Filtered results.
	 */
	private function get_filtered_results_by_severity( $results, $error_severity, $warning_severity, $include_low_severity_errors = false, $include_low_severity_warnings = false ) {
		$errors   = array();
		$warnings = array();

		foreach ( $results as $item ) {
			if ( 'ERROR' === $item['type'] && $item['severity'] >= $error_severity ) {
				$errors[] = $item;
			} elseif ( $include_low_severity_errors && 'ERROR' === $item['type'] && $item['severity'] < $error_severity ) {
				$item['type'] = 'ERROR_LOW_SEVERITY';
				$errors[]     = $item;
			} elseif ( $include_low_severity_warnings && 'WARNING' === $item['type'] && $item['severity'] < $warning_severity ) {
				$item['type'] = 'WARNING_LOW_SEVERITY';
				$warnings[]   = $item;
			} elseif ( 'WARNING' === $item['type'] && $item['severity'] >= $warning_severity ) {
				$warnings[] = $item;
			}
		}

		return array_merge( $errors, $warnings );
	}

	/**
	 * Returns check results filtered by ignore codes.
	 *
	 * @since 1.4.0
	 *
	 * @param array $results      Check results.
	 * @param array $ignore_codes Array of error codes to be ignored.
	 * @return array Filtered results.
	 */
	private function get_filtered_results_by_ignore_codes( $results, $ignore_codes ) {
		return array_filter(
			$results,
			static function ( $result ) use ( $ignore_codes ) {
				return ! in_array( $result['code'], $ignore_codes, true );
			}
		);
	}
}
