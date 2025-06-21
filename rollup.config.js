import terser from '@rollup/plugin-terser';
import strip from '@rollup/plugin-strip';

const isProduction = process.env.NODE_ENV === 'production';

export default {
  input: 'assets/mblock.js',
  output: {
    file: 'assets/dist/mblock.min.js',
    format: 'iife',
    name: 'MBlock',
    sourcemap: !isProduction
  },
  plugins: [
    // Remove console.log statements in production
    isProduction && strip({
      debugger: true,
      functions: ['console.*', 'assert.*'],
      sourceMap: false
    }),
    // Minify in production
    isProduction && terser({
      compress: {
        drop_console: true,
        drop_debugger: true,
        pure_funcs: ['console.log', 'console.info', 'console.debug']
      },
      mangle: {
        // Preserve function names that might be called externally
        reserved: [
          'mblock_init',
          'mblock_add_item',
          'mblock_moveup',
          'mblock_movedown',
          'mblock_reinit_widgets',
          'mblock_debug_move_buttons'
        ]
      }
    })
  ].filter(Boolean)
};
