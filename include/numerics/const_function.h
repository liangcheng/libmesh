
#ifndef __const_function_h__
#define __const_function_h__

// Local includes
#include "libmesh/dense_vector.h"
#include "libmesh/function_base.h"
#include "libmesh/point.h"

// C++ includes
#include <string>

namespace libMesh {

template <typename Output=Number>
class ConstFunction : public FunctionBase<Output>
{
public:
  explicit
  ConstFunction (const Output &c) : _c(c) { this->_initialized = true; }

  virtual Output operator() (const Point&,
                             const Real = 0)
    { return _c; }

  virtual void operator() (const Point&,
                           const Real,
                           DenseVector<Output>& output)
    {
      unsigned int size = output.size();
      for (unsigned int i=0; i != size; ++i)
        output(i) = _c;
    }

  virtual AutoPtr<FunctionBase<Output> > clone() const {
    return AutoPtr<FunctionBase<Output> > 
      (new ConstFunction<Output>(_c));
  }

private:
  Output _c;
};

} // namespace libMesh

#endif // __const_function_h__
